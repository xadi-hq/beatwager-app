<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientPointsException;
use App\Models\Challenge;
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
            // TODO: Add event dispatch when events are implemented

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
            throw new \Exception('Challenge cannot be submitted at this time');
        }

        $challenge->update([
            'submitted_at' => now(),
            'submission_note' => $note,
            'submission_media' => $media,
        ]);

        // Dispatch challenge submitted event
        // TODO: Add event dispatch when events are implemented

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
            // TODO: Add event dispatch when events are implemented

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

        return DB::transaction(function () use ($challenge, $reason) {
            // Release hold back to payer
            $this->releaseHold($challenge);

            // Update challenge status
            $challenge->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $reason,
            ]);

            // Dispatch challenge rejected event
            // TODO: Add event dispatch when events are implemented

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
        // TODO: Add event dispatch when events are implemented

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
            // TODO: Add event dispatch when events are implemented

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
                // TODO: Add event dispatch when events are implemented
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
            null, // no wager
            null, // no wager entry
            $challenge
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
            null, // no wager
            null, // no wager entry
            $challenge
        );
    }
}