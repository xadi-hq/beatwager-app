<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Enums\DisputeVoteOutcome;
use App\Enums\TransactionType;
use App\Events\DisputeCreated;
use App\Events\DisputeResolved;
use App\Events\DisputeVoteReceived;
use App\Models\Challenge;
use App\Models\Dispute;
use App\Models\DisputeVote;
use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DisputeService
{
    public function __construct(
        private readonly PointService $pointService
    ) {}

    /**
     * Create a new dispute for a settleable item.
     */
    public function createDispute(Model $item, User $reporter): Dispute
    {
        // Validate item can be disputed
        if (!method_exists($item, 'canBeDisputed') || !$item->canBeDisputed()) {
            throw new \InvalidArgumentException('This item cannot be disputed.');
        }

        $settler = $item->getSettler();
        if (!$settler) {
            throw new \InvalidArgumentException('Cannot identify the settler for this item.');
        }

        $group = $item->group;
        $isSelfReport = $reporter->id === $settler->id;

        return DB::transaction(function () use ($item, $reporter, $settler, $group, $isSelfReport) {
            $dispute = Dispute::create([
                'group_id' => $group->id,
                'disputable_type' => get_class($item),
                'disputable_id' => $item->id,
                'reporter_id' => $reporter->id,
                'accused_id' => $settler->id,
                'is_self_report' => $isSelfReport,
                'status' => DisputeStatus::Pending,
                'original_outcome' => $item->getOutcomeValue(),
                'votes_required' => Dispute::calculateVotesRequired($group, $reporter, $settler),
                'expires_at' => now()->addHours(48),
            ]);

            // Link dispute to item
            $item->update(['dispute_id' => $dispute->id]);

            // Update item status to disputed (for wagers)
            if ($item instanceof Wager) {
                $item->update(['status' => 'disputed']);
            }

            // Fire event for notifications
            event(new DisputeCreated($dispute));

            return $dispute;
        });
    }

    /**
     * Create an elimination dispute to report a participant who should have tapped out.
     */
    public function createEliminationDispute(
        Challenge $challenge,
        User $reporter,
        User $accusedParticipant
    ): Dispute {
        // Validate this is an elimination challenge
        if (!$challenge->isEliminationChallenge()) {
            throw new \InvalidArgumentException('This is not an elimination challenge.');
        }

        // Validate challenge can be disputed
        if (!$challenge->canBeDisputed()) {
            throw new \InvalidArgumentException('This challenge cannot be disputed.');
        }

        // Validate accused is a survivor
        $participant = $challenge->participants()
            ->where('user_id', $accusedParticipant->id)
            ->whereNull('eliminated_at')
            ->first();

        if (!$participant) {
            throw new \InvalidArgumentException('The accused user is not an active participant.');
        }

        $group = $challenge->group;

        return DB::transaction(function () use ($challenge, $reporter, $accusedParticipant, $group) {
            $dispute = Dispute::create([
                'group_id' => $group->id,
                'disputable_type' => Challenge::class,
                'disputable_id' => $challenge->id,
                'reporter_id' => $reporter->id,
                'accused_id' => $accusedParticipant->id,
                'is_self_report' => false, // Elimination disputes are always third-party reports
                'status' => DisputeStatus::Pending,
                'original_outcome' => 'survivor', // The accused is currently a survivor
                'votes_required' => Dispute::calculateVotesRequired($group, $reporter, $accusedParticipant),
                'expires_at' => now()->addHours(48),
            ]);

            // Link dispute to challenge
            $challenge->update(['dispute_id' => $dispute->id]);

            // Fire event for notifications
            event(new DisputeCreated($dispute));

            return $dispute;
        });
    }

    /**
     * Cast a vote on a dispute.
     */
    public function castVote(
        Dispute $dispute,
        User $voter,
        DisputeVoteOutcome $outcome,
        ?string $selectedOutcome = null
    ): DisputeVote {
        // Validate voter can vote
        if (!$dispute->canUserVote($voter)) {
            throw new \InvalidArgumentException('You are not eligible to vote on this dispute.');
        }

        // Validate selected_outcome is provided for DifferentOutcome votes
        if ($outcome === DisputeVoteOutcome::DifferentOutcome && empty($selectedOutcome)) {
            throw new \InvalidArgumentException('You must specify the correct outcome.');
        }

        return DB::transaction(function () use ($dispute, $voter, $outcome, $selectedOutcome) {
            $vote = DisputeVote::create([
                'dispute_id' => $dispute->id,
                'voter_id' => $voter->id,
                'vote_outcome' => $outcome,
                'selected_outcome' => $selectedOutcome,
            ]);

            // Fire event
            event(new DisputeVoteReceived($vote));

            // Check if we should resolve
            if ($dispute->hasEnoughVotes()) {
                $this->attemptResolution($dispute);
            }

            return $vote;
        });
    }

    /**
     * Attempt to resolve a dispute based on current votes.
     */
    public function attemptResolution(Dispute $dispute): bool
    {
        $dispute->refresh();

        if (!$dispute->isPending()) {
            return false;
        }

        $winningOutcome = $dispute->determineWinningOutcome();
        if ($winningOutcome === null) {
            return false; // No clear majority yet
        }

        return $this->resolveDispute($dispute, $winningOutcome);
    }

    /**
     * Resolve a dispute with the given outcome.
     */
    public function resolveDispute(Dispute $dispute, DisputeVoteOutcome $winningOutcome): bool
    {
        return DB::transaction(function () use ($dispute, $winningOutcome) {
            $resolution = $winningOutcome->toResolution();

            // Handle self-report as honest mistake (different resolution path)
            if ($dispute->is_self_report && $resolution === DisputeResolution::FraudConfirmed) {
                $this->applyHonestMistakePenalty($dispute);
                $resolution = DisputeResolution::FraudConfirmed; // Still mark as fraud for correction
            } else {
                // Apply appropriate penalty based on resolution
                match ($resolution) {
                    DisputeResolution::OriginalCorrect => $this->applyFalseDisputePenalty($dispute),
                    DisputeResolution::FraudConfirmed => $this->applyFraudPenalty($dispute),
                    DisputeResolution::PrematureSettlement => $this->applyPrematureSettlementPenalty($dispute),
                };
            }

            // Get corrected outcome if applicable
            $correctedOutcome = null;
            if ($resolution === DisputeResolution::FraudConfirmed) {
                $correctedOutcome = $dispute->getCorrectedOutcomeFromVotes();
            }

            // Update dispute
            $dispute->update([
                'status' => DisputeStatus::Resolved,
                'resolution' => $resolution,
                'corrected_outcome' => $correctedOutcome,
                'resolved_at' => now(),
            ]);

            // Clear dispute from item
            $item = $dispute->disputable;
            $item->update(['dispute_id' => null]);

            // Apply outcome correction if needed
            if ($resolution === DisputeResolution::FraudConfirmed && $correctedOutcome) {
                $this->correctOutcome($dispute, $correctedOutcome);
            } elseif ($resolution === DisputeResolution::PrematureSettlement) {
                $this->clearSettlement($dispute);
            }

            // Fire event
            event(new DisputeResolved($dispute));

            return true;
        });
    }

    /**
     * Handle dispute expiration (called by scheduler).
     */
    public function handleExpiredDispute(Dispute $dispute): void
    {
        if (!$dispute->isExpired() || !$dispute->isPending()) {
            return;
        }

        DB::transaction(function () use ($dispute) {
            // If we have any votes, try to resolve with them
            if ($dispute->getVoteCount() > 0) {
                $winningOutcome = $dispute->determineWinningOutcome();
                if ($winningOutcome) {
                    $this->resolveDispute($dispute, $winningOutcome);
                    return;
                }
            }

            // No votes or no clear winner - dismiss the dispute (original stands)
            $dispute->update([
                'status' => DisputeStatus::Resolved,
                'resolution' => DisputeResolution::OriginalCorrect,
                'resolved_at' => now(),
            ]);

            // Clear dispute from item and restore status
            $item = $dispute->disputable;
            $item->update(['dispute_id' => null]);

            if ($item instanceof Wager && $item->status === 'disputed') {
                $item->update(['status' => 'settled']);
            }

            // Penalize reporter for unresolved dispute
            $this->applyFalseDisputePenalty($dispute);

            event(new DisputeResolved($dispute));
        });
    }

    /**
     * Apply penalty for false dispute (reporter pays 10%).
     */
    private function applyFalseDisputePenalty(Dispute $dispute): void
    {
        $this->pointService->deductPercentage(
            $dispute->reporter,
            $dispute->group,
            10,
            TransactionType::DisputePenaltyFalseReport,
            $dispute
        );
    }

    /**
     * Apply penalty for honest mistake (self-reporter pays 5%).
     */
    private function applyHonestMistakePenalty(Dispute $dispute): void
    {
        $this->pointService->deductPercentage(
            $dispute->accused,
            $dispute->group,
            5,
            TransactionType::DisputePenaltyHonestMistake,
            $dispute
        );
    }

    /**
     * Apply penalty for confirmed fraud (25% first, 50% repeat).
     */
    private function applyFraudPenalty(Dispute $dispute): void
    {
        $accused = $dispute->accused;
        $messengerService = $accused->getMessengerService($dispute->group->platform);

        if (!$messengerService) {
            throw new \RuntimeException('Cannot find messenger service for accused user.');
        }

        $penaltyPercentage = $messengerService->getFraudPenaltyPercentage();

        $this->pointService->deductPercentage(
            $accused,
            $dispute->group,
            $penaltyPercentage,
            TransactionType::DisputePenaltyFraud,
            $dispute
        );

        // Increment fraud count
        $messengerService->incrementFraudCount();
    }

    /**
     * Apply penalty for premature settlement (25% first, 50% repeat + ban).
     */
    private function applyPrematureSettlementPenalty(Dispute $dispute): void
    {
        $accused = $dispute->accused;
        $messengerService = $accused->getMessengerService($dispute->group->platform);

        if (!$messengerService) {
            throw new \RuntimeException('Cannot find messenger service for accused user.');
        }

        $penaltyPercentage = $messengerService->getFraudPenaltyPercentage();

        $this->pointService->deductPercentage(
            $accused,
            $dispute->group,
            $penaltyPercentage,
            TransactionType::DisputePenaltyPremature,
            $dispute
        );

        // Increment fraud count
        $messengerService->incrementFraudCount();

        // Ban user from item (handled in clearSettlement)
    }

    /**
     * Correct the outcome of an item after fraud is confirmed.
     */
    private function correctOutcome(Dispute $dispute, string $correctedOutcome): void
    {
        $item = $dispute->disputable;

        if ($item instanceof Wager) {
            // Reverse original settlement and apply correct outcome
            app(WagerService::class)->reverseAndResettleWager($item, $correctedOutcome, $dispute);
        } elseif ($item instanceof Challenge) {
            // Handle elimination challenges differently
            if ($item->isEliminationChallenge() && $correctedOutcome === 'should_be_eliminated') {
                // Force elimination of the accused participant
                app(EliminationChallengeService::class)->forceElimination(
                    $item,
                    $dispute->accused,
                    $dispute,
                    "Eliminated by group vote - dispute #{$dispute->id}"
                );
            } else {
                // Reverse challenge verification and apply correct outcome
                app(ChallengeService::class)->reverseAndResettleChallenge($item, $correctedOutcome, $dispute);
            }
        }
    }

    /**
     * Clear settlement for premature settlement cases.
     */
    private function clearSettlement(Dispute $dispute): void
    {
        $item = $dispute->disputable;
        $bannedUserId = $dispute->accused_id;

        if ($item instanceof Wager) {
            app(WagerService::class)->clearSettlementAndBanUser($item, $bannedUserId, $dispute);
        } elseif ($item instanceof Challenge) {
            app(ChallengeService::class)->clearSettlementAndBanUser($item, $bannedUserId, $dispute);
        }
    }

    /**
     * Get all pending disputes for a group.
     */
    public function getPendingDisputesForGroup(Group $group): \Illuminate\Database\Eloquent\Collection
    {
        return Dispute::where('group_id', $group->id)
            ->pending()
            ->with(['disputable', 'reporter', 'accused', 'votes'])
            ->orderBy('expires_at')
            ->get();
    }

    /**
     * Get all expired disputes that need handling.
     */
    public function getExpiredDisputes(): \Illuminate\Database\Eloquent\Collection
    {
        return Dispute::expired()->get();
    }
}
