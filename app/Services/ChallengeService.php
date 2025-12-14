<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Exceptions\InsufficientPointsException;
use App\Models\Challenge;
use App\Models\Dispute;
use App\Models\Group;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChallengeService
{
    public function __construct(
        private readonly PointService $pointService
    ) {}

    /**
     * Create a new challenge
     */
    public function createChallenge(
        Group $group,
        User $creator,
        array $data
    ): Challenge {
        return DB::transaction(function () use ($group, $creator, $data) {
            $challenge = Challenge::create([
                'id' => Str::uuid(),
                'group_id' => $group->id,
                'creator_id' => $creator->id,
                'description' => $data['description'],
                'amount' => $data['amount'],
                'completion_deadline' => $data['completion_deadline'],
                'acceptance_deadline' => $data['acceptance_deadline'] ?? null,
                'status' => 'open',
            ]);

            // Dispatch challenge created event
            \App\Events\ChallengeCreated::dispatch($challenge);

            return $challenge->load(['group', 'creator']);
        });
    }

    /**
     * Accept a challenge (first-come, first-served)
     */
    public function acceptChallenge(
        Challenge $challenge,
        User $acceptor
    ): Challenge {
        return DB::transaction(function () use ($challenge, $acceptor) {
            // Lock the challenge row for update to prevent race conditions
            $challenge = Challenge::where('id', $challenge->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (!$challenge) {
                throw new \Exception('Challenge is no longer available for acceptance');
            }

            if ($challenge->creator_id === $acceptor->id) {
                throw new \Exception('You cannot accept your own challenge');
            }

            if ($challenge->isAcceptanceExpired()) {
                throw new \Exception('Challenge acceptance deadline has passed');
            }

            // Determine who pays based on challenge type
            // Type 1 (offering payment): creator pays
            // Type 2 (offering service): acceptor pays
            $payer = $challenge->isOfferingPayment() ? $challenge->creator : $acceptor;

            // Reserve points from the payer
            $holdTransaction = $this->reservePoints(
                $payer,
                $challenge->group,
                $challenge->getAbsoluteAmount(),
                $challenge
            );

            // Update challenge
            $challenge->update([
                'acceptor_id' => $acceptor->id,
                'status' => 'accepted',
                'accepted_at' => now(),
                'hold_transaction_id' => $holdTransaction->id,
            ]);

            // Dispatch challenge accepted event
            \App\Events\ChallengeAccepted::dispatch($challenge, $acceptor);

            return $challenge->load(['group', 'creator', 'acceptor']);
        });
    }

    /**
     * Submit challenge completion
     */
    public function submitChallenge(
        Challenge $challenge,
        User $user,
        ?string $note = null,
        ?array $media = null
    ): Challenge {
        if ($challenge->acceptor_id !== $user->id) {
            throw new \Exception('Only the acceptor can submit the challenge');
        }

        if (!$challenge->canBeSubmitted()) {
            if (!$challenge->isAccepted()) {
                throw new \Exception('Challenge must be accepted before it can be submitted (current status: ' . $challenge->status . ')');
            }
            if ($challenge->isPastSubmissionGracePeriod()) {
                throw new \Exception('Submission grace period has passed (24 hours after completion deadline)');
            }
            throw new \Exception('Challenge cannot be submitted at this time');
        }

        $challenge->update([
            'submitted_at' => now(),
            'submission_note' => $note,
            'submission_media' => $media,
        ]);

        // Dispatch challenge submitted event
        \App\Events\ChallengeSubmitted::dispatch($challenge, $user);

        return $challenge->load(['group', 'creator', 'acceptor']);
    }

    /**
     * Approve challenge completion
     * The payer (person who pays the points) approves the work
     */
    public function approveChallenge(
        Challenge $challenge,
        User $approver
    ): Challenge {
        // The payer approves the work (creator for Type 1, acceptor for Type 2)
        $expectedApprover = $challenge->getPayer();

        if ($expectedApprover->id !== $approver->id) {
            throw new \Exception('Only the payer can approve the challenge');
        }

        if (!$challenge->isAwaitingReview()) {
            throw new \Exception('Challenge is not awaiting review');
        }

        return DB::transaction(function () use ($challenge, $approver) {
            // Settle points from hold to payee
            $this->settleHoldToAcceptor($challenge);

            // Update challenge status
            $challenge->update([
                'status' => 'completed',
                'verified_at' => now(),
                'verified_by_id' => $approver->id,
                'completed_at' => now(),
            ]);

            // Dispatch challenge approved event
            \App\Events\ChallengeApproved::dispatch($challenge, $approver);

            return $challenge->load(['group', 'creator', 'acceptor', 'verifiedBy']);
        });
    }

    /**
     * Reject challenge completion
     * The payer (person who pays the points) can reject the work
     */
    public function rejectChallenge(
        Challenge $challenge,
        User $rejecter,
        string $reason
    ): Challenge {
        // The payer rejects the work (creator for Type 1, acceptor for Type 2)
        $expectedRejecter = $challenge->getPayer();

        if ($expectedRejecter->id !== $rejecter->id) {
            throw new \Exception('Only the payer can reject the challenge');
        }

        if (!$challenge->isAwaitingReview()) {
            throw new \Exception('Challenge is not awaiting review');
        }

        return DB::transaction(function () use ($challenge, $reason, $rejecter) {
            // Release hold back to payer
            $this->releaseHold($challenge);

            // Update challenge status
            $challenge->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $reason,
            ]);

            // Dispatch challenge rejected event
            \App\Events\ChallengeRejected::dispatch($challenge, $rejecter);

            return $challenge->load(['group', 'creator', 'acceptor']);
        });
    }

    /**
     * Cancel an open challenge
     */
    public function cancelChallenge(
        Challenge $challenge,
        User $creator
    ): Challenge {
        if ($challenge->creator_id !== $creator->id) {
            throw new \Exception('Only the creator can cancel the challenge');
        }

        if (!$challenge->isOpen()) {
            throw new \Exception('Only open challenges can be cancelled');
        }

        $challenge->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by_id' => $creator->id,
        ]);

        // Dispatch challenge cancelled event
        \App\Events\ChallengeCancelled::dispatch($challenge, $creator);

        return $challenge->load(['group', 'creator', 'cancelledBy']);
    }

    /**
     * Expire open challenges past acceptance deadline
     */
    public function expireOpenChallenges(): int
    {
        $expiredChallenges = Challenge::open()
            ->whereNotNull('acceptance_deadline')
            ->where('acceptance_deadline', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredChallenges as $challenge) {
            $challenge->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'failure_reason' => 'Acceptance deadline passed',
            ]);

            // Dispatch challenge expired event
            \App\Events\ChallengeExpired::dispatch($challenge);

            $count++;
        }

        return $count;
    }

    /**
     * Fail accepted challenges past completion deadline
     */
    public function failPastDeadlineChallenges(): int
    {
        $pastDeadlineChallenges = Challenge::accepted()
            ->where('completion_deadline', '<', now())
            ->whereNull('verified_at')
            ->with('holdTransaction')
            ->get();

        $count = 0;
        foreach ($pastDeadlineChallenges as $challenge) {
            DB::transaction(function () use ($challenge) {
                // Release hold back to creator
                $this->releaseHold($challenge);

                $challenge->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => 'Completion deadline passed',
                ]);

                // Dispatch challenge deadline missed event
                \App\Events\ChallengeDeadlineMissed::dispatch($challenge);
            });

            $count++;
        }

        return $count;
    }

    /**
     * Reserve points from creator (create hold)
     */
    private function reservePoints(
        User $user,
        Group $group,
        int $amount,
        Challenge $challenge
    ): Transaction {
        $balanceBefore = $this->pointService->getBalance($user, $group);

        if ($balanceBefore < $amount) {
            throw new InsufficientPointsException($amount, $balanceBefore);
        }

        $balanceAfter = $balanceBefore - $amount;

        // Update user balance (reserve the points)
        DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->update([
                'points' => $balanceAfter,
                'last_activity_at' => now(),
            ]);

        // Create hold transaction
        return Transaction::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'type' => 'challenge_hold',
            'amount' => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'challenge_id' => $challenge->id,
            'description' => 'Challenge hold: ' . Str::limit($challenge->description, 50),
        ]);
    }

    /**
     * Settle hold points to the payee
     */
    private function settleHoldToAcceptor(Challenge $challenge): void
    {
        if (!$challenge->holdTransaction) {
            throw new \Exception('No hold transaction found for challenge');
        }

        // Award points to the payee (acceptor for Type 1, creator for Type 2)
        $payee = $challenge->getPayee();

        $this->pointService->awardPoints(
            $payee,
            $challenge->group,
            $challenge->getAbsoluteAmount(),
            'challenge_completed',
            $challenge  // Pass the challenge
        );
    }

    /**
     * Release hold back to the payer
     */
    private function releaseHold(Challenge $challenge): void
    {
        if (!$challenge->holdTransaction) {
            return; // No hold to release
        }

        // Return points to the payer (creator for Type 1, acceptor for Type 2)
        $payer = $challenge->getPayer();

        $this->pointService->awardPoints(
            $payer,
            $challenge->group,
            $challenge->getAbsoluteAmount(),
            'challenge_failed',
            $challenge  // Pass the challenge
        );
    }

    /**
     * Reverse a challenge verification and re-verify with corrected outcome.
     * Used when a dispute confirms fraud.
     */
    public function reverseAndResettleChallenge(Challenge $challenge, string $correctedOutcome, Dispute $dispute): void
    {
        DB::transaction(function () use ($challenge, $correctedOutcome, $dispute) {
            // Step 1: Reverse the completion transaction
            $this->reverseCompletionTransaction($challenge, $dispute);

            // Step 2: Apply the correct outcome
            if ($correctedOutcome === 'completed') {
                // Should have been completed - transfer to payee
                $this->settleHoldToAcceptor($challenge);
                $challenge->update([
                    'status' => 'completed',
                    'verified_at' => now(),
                ]);
            } else {
                // Should have been failed - release hold
                $this->releaseHold($challenge);
                $challenge->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => "Corrected via dispute #{$dispute->id}",
                ]);
            }

            // Audit log
            AuditService::log(
                action: 'challenge.dispute_corrected',
                auditable: $challenge,
                metadata: [
                    'dispute_id' => $dispute->id,
                    'original_outcome' => $dispute->original_outcome,
                    'corrected_outcome' => $correctedOutcome,
                ]
            );
        });
    }

    /**
     * Clear a challenge verification for premature settlement cases.
     * Bans the specified user from the challenge.
     */
    public function clearSettlementAndBanUser(Challenge $challenge, string $bannedUserId, Dispute $dispute): void
    {
        DB::transaction(function () use ($challenge, $bannedUserId, $dispute) {
            // Step 1: Reverse the completion transaction
            $this->reverseCompletionTransaction($challenge, $dispute);

            // Step 2: Reset challenge to accepted state
            $challenge->update([
                'status' => 'accepted',
                'verified_at' => null,
                'verified_by_id' => null,
                'completed_at' => null,
                'failed_at' => null,
                'failure_reason' => null,
            ]);

            // Note: For challenges, "banning" differs from wagers:
            // - Wagers: The banned user's entry is removed from participation
            // - Challenges: The challenge state is reset for re-verification
            // Since challenges are 1:1 (creator vs acceptor), we can't remove participants
            // Instead, resetting allows proper re-verification by other party or timeout

            // Audit log
            AuditService::log(
                action: 'challenge.settlement_cleared',
                auditable: $challenge,
                metadata: [
                    'dispute_id' => $dispute->id,
                    'banned_user_id' => $bannedUserId,
                    'reason' => 'premature_settlement',
                ]
            );
        });
    }

    /**
     * Reverse completion transaction for a challenge.
     */
    private function reverseCompletionTransaction(Challenge $challenge, Dispute $dispute): void
    {
        // Find completion transaction
        $completionTransaction = Transaction::where('transactionable_type', Challenge::class)
            ->where('transactionable_id', $challenge->id)
            ->where('type', 'challenge_completed')
            ->first();

        if ($completionTransaction && $completionTransaction->amount > 0) {
            // This was a credit to payee - need to deduct
            $payee = $challenge->getPayee();
            $this->pointService->deductPoints(
                $payee,
                $challenge->group,
                $completionTransaction->amount,
                TransactionType::DisputeCorrection->value,
                $dispute
            );
        }

        // Also check for failed transaction (hold release)
        $failedTransaction = Transaction::where('transactionable_type', Challenge::class)
            ->where('transactionable_id', $challenge->id)
            ->where('type', 'challenge_failed')
            ->first();

        if ($failedTransaction && $failedTransaction->amount > 0) {
            // Hold was released to payer - need to deduct
            $payer = $challenge->getPayer();
            $this->pointService->deductPoints(
                $payer,
                $challenge->group,
                $failedTransaction->amount,
                TransactionType::DisputeCorrection->value,
                $dispute
            );
        }
    }
}