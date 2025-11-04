<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ChallengeType;
use App\Enums\NudgeResponse;
use App\Enums\TransactionType;
use App\Enums\ValidationStatus;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Group;
use App\Models\SuperChallengeNudge;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuperChallengeService
{
    public function __construct(
        private readonly PointService $pointService
    ) {}

    /**
     * Check groups eligible for SuperChallenge and send nudges
     */
    public function processEligibleGroups(): void
    {
        $eligibleGroups = $this->getEligibleGroups();

        foreach ($eligibleGroups as $group) {
            $this->sendNudgeToRandomUser($group);
        }
    }

    /**
     * Get groups eligible for a new SuperChallenge
     */
    private function getEligibleGroups()
    {
        // Get all potentially eligible groups by date/frequency
        $groups = Group::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('last_superchallenge_at')
                    ->orWhereRaw($this->getEligibilityQuery());
            })
            ->with('users')
            ->get();

        // Filter to only groups with 3+ members and no pending nudges
        return $groups->filter(function ($group) {
            if ($group->users->count() < 3) {
                return false;
            }

            // Check if group has any pending nudges
            $hasPendingNudge = SuperChallengeNudge::where('group_id', $group->id)
                ->where('response', NudgeResponse::PENDING)
                ->exists();

            return !$hasPendingNudge;
        });
    }

    /**
     * Build SQL query for eligibility based on frequency
     */
    private function getEligibilityQuery(): string
    {
        return "
            (superchallenge_frequency = 'weekly' AND last_superchallenge_at <= NOW() - INTERVAL '7 days') OR
            (superchallenge_frequency = 'monthly' AND last_superchallenge_at <= NOW() - INTERVAL '1 month') OR
            (superchallenge_frequency = 'quarterly' AND last_superchallenge_at <= NOW() - INTERVAL '3 months')
        ";
    }

    /**
     * Send nudge to a random active user in the group
     */
    private function sendNudgeToRandomUser(Group $group): void
    {
        // Get active users (posted/joined wager in last 14 days)
        // Exclude users who were recently nudged (within 3 months)
        $recentlyNudgedUserIds = SuperChallengeNudge::forGroup($group->id)
            ->where('nudged_at', '>=', now()->subMonths(3))
            ->pluck('nudged_user_id');

        // Prefer active users (with recent transactions)
        $eligibleUser = $group->users()
            ->whereHas('transactions', function ($query) use ($group) {
                $query->where('group_id', $group->id)
                    ->where('created_at', '>=', now()->subDays(14));
            })
            ->whereNotIn('users.id', $recentlyNudgedUserIds)
            ->inRandomOrder()
            ->first();

        // Fallback to any group member if no active users found
        if (!$eligibleUser) {
            $eligibleUser = $group->users()
                ->whereNotIn('users.id', $recentlyNudgedUserIds)
                ->inRandomOrder()
                ->first();
        }

        // If still no users (e.g., all recently nudged), skip
        if (!$eligibleUser) {
            return;
        }

        // Create nudge record
        $nudge = SuperChallengeNudge::create([
            'id' => Str::uuid(),
            'group_id' => $group->id,
            'nudged_user_id' => $eligibleUser->id,
            'nudged_at' => now(),
            'response' => NudgeResponse::PENDING,
        ]);

        // Dispatch event to send Telegram DM
        \App\Events\SuperChallengeNudgeSent::dispatch($nudge);
    }

    /**
     * Create SuperChallenge from nudged user's input
     */
    public function createSuperChallenge(
        SuperChallengeNudge $nudge,
        string $description,
        int $deadlineInDays,
        ?string $evidenceGuidance = null
    ): Challenge {
        return DB::transaction(function () use ($nudge, $description, $deadlineInDays, $evidenceGuidance) {
            $group = $nudge->group;
            $creator = $nudge->nudgedUser;

            // Calculate prize per person and max participants
            [$prizePerPerson, $maxParticipants] = $this->calculatePrizeStructure($group);

            $challenge = Challenge::create([
                'id' => Str::uuid(),
                'type' => ChallengeType::SUPER_CHALLENGE,
                'group_id' => $group->id,
                'creator_id' => $creator->id,
                'description' => $description,
                'prize_per_person' => $prizePerPerson,
                'max_participants' => $maxParticipants,
                'evidence_guidance' => $evidenceGuidance,
                'completion_deadline' => now()->addDays($deadlineInDays),
                'status' => 'open',
            ]);

            // Update group's last SuperChallenge timestamp
            $group->update(['last_superchallenge_at' => now()]);

            // Update nudge record with created challenge
            $nudge->update([
                'response' => NudgeResponse::ACCEPTED,
                'responded_at' => now(),
                'created_challenge_id' => $challenge->id,
            ]);

            // Dispatch event to announce in group
            \App\Events\SuperChallengeCreated::dispatch($challenge);

            return $challenge->load(['group', 'creator']);
        });
    }

    /**
     * Calculate prize per person and max participants
     *
     * @return array [prizePerPerson, maxParticipants]
     */
    private function calculatePrizeStructure(Group $group): array
    {
        $totalGroupPoints = $group->users()->sum('points');

        // Random percentage between 2-5%
        $percentage = rand(2, 5);
        $rawPrize = ($totalGroupPoints * $percentage) / 100;

        // Round to nearest 50
        $prizePerPerson = round($rawPrize / 50) * 50;

        // Apply per-person bounds (50-150)
        $prizePerPerson = max(50, min(150, (int) $prizePerPerson));

        // Calculate max participants (never mint more than 1,000 pts total)
        $maxTotalPrize = 1000;
        $maxParticipants = (int) floor($maxTotalPrize / $prizePerPerson);

        // Cap at 10 participants
        $maxParticipants = min(10, $maxParticipants);

        return [$prizePerPerson, $maxParticipants];
    }

    /**
     * User accepts SuperChallenge
     */
    public function acceptChallenge(Challenge $challenge, User $user): ChallengeParticipant
    {
        return DB::transaction(function () use ($challenge, $user) {
            // Validate
            if (!$challenge->isSuperChallenge()) {
                throw new \InvalidArgumentException('This is not a SuperChallenge');
            }

            if ($challenge->creator_id === $user->id) {
                throw new \InvalidArgumentException('Creator cannot accept their own SuperChallenge');
            }

            if ($challenge->hasReachedMaxParticipants()) {
                throw new \InvalidArgumentException('Challenge has reached maximum participants');
            }

            if ($challenge->completion_deadline->isPast()) {
                throw new \InvalidArgumentException('Challenge deadline has passed');
            }

            // Check if already accepted
            $existing = ChallengeParticipant::where('challenge_id', $challenge->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                throw new \InvalidArgumentException('User has already accepted this challenge');
            }

            // Create participation record
            $participant = ChallengeParticipant::create([
                'id' => Str::uuid(),
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'accepted_at' => now(),
                'validation_status' => ValidationStatus::PENDING,
            ]);

            // Award creator +50 bonus if this is the first acceptance
            if ($challenge->participants()->count() === 1) {
                $this->awardAcceptanceBonus($challenge);
            }

            // Dispatch event
            \App\Events\SuperChallengeAccepted::dispatch($challenge, $user);

            return $participant->load(['challenge', 'user']);
        });
    }

    /**
     * Award creator +50 point bonus for first acceptance
     */
    public function awardAcceptanceBonus(Challenge $challenge): void
    {
        if (!$challenge->creator_id) {
            return;
        }

        $this->pointService->awardPoints(
            $challenge->creator,
            $challenge->group,
            50,
            TransactionType::SuperChallengeAcceptanceBonus->value,
            $challenge
        );
    }

    /**
     * User claims completion (evidence will be in Telegram chat)
     */
    public function claimCompletion(
        Challenge $challenge,
        User $user
    ): ChallengeParticipant {
        return DB::transaction(function () use ($challenge, $user) {
            $participant = ChallengeParticipant::where('challenge_id', $challenge->id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($participant->hasCompleted()) {
                throw new \InvalidArgumentException('You have already submitted completion for this challenge');
            }

            $participant->update([
                'completed_at' => now(),
            ]);

            // Dispatch event to notify creator for validation
            // Creator will see evidence in Telegram chat (screenshot/message)
            \App\Events\SuperChallengeCompletionClaimed::dispatch($participant);

            return $participant->load(['challenge', 'user']);
        });
    }

    /**
     * Creator validates or rejects a completion claim
     */
    public function validateCompletion(
        ChallengeParticipant $participant,
        User $creator,
        bool $approve
    ): void {
        DB::transaction(function () use ($participant, $creator, $approve) {
            $challenge = $participant->challenge;

            // Validate creator
            if ($challenge->creator_id !== $creator->id) {
                throw new \InvalidArgumentException('Only the creator can validate completions');
            }

            if (!$participant->hasCompleted()) {
                throw new \InvalidArgumentException('Participant has not completed the challenge yet');
            }

            if (!$participant->isPending()) {
                throw new \InvalidArgumentException('This completion has already been validated');
            }

            if ($approve) {
                // Approve and distribute prize
                $participant->update([
                    'validation_status' => ValidationStatus::VALIDATED,
                    'validated_by_creator_at' => now(),
                ]);

                $this->distributePrize($participant);
                $this->awardValidationBonus($challenge, $creator);
            } else {
                // Reject
                $participant->update([
                    'validation_status' => ValidationStatus::REJECTED,
                    'validated_by_creator_at' => now(),
                ]);
            }

            // Dispatch event
            \App\Events\SuperChallengeCompletionValidated::dispatch($participant, $approve);
        });
    }

    /**
     * Distribute prize to validated participant
     */
    public function distributePrize(ChallengeParticipant $participant): void
    {
        $challenge = $participant->challenge;

        $transaction = $this->pointService->awardPoints(
            $participant->user,
            $challenge->group,
            $challenge->prize_per_person,
            TransactionType::SuperChallengePrize->value,
            $challenge
        );

        $participant->update(['prize_transaction_id' => $transaction->id]);
    }

    /**
     * Award creator +25 point validation bonus
     */
    public function awardValidationBonus(Challenge $challenge, User $creator): void
    {
        $this->pointService->awardPoints(
            $creator,
            $challenge->group,
            25,
            TransactionType::SuperChallengeValidationBonus->value,
            $challenge
        );
    }

    /**
     * Auto-approve completions after 48h timeout
     */
    public function processAutoApprovals(): void
    {
        $needingAutoApproval = ChallengeParticipant::needingAutoValidation()->get();

        foreach ($needingAutoApproval as $participant) {
            DB::transaction(function () use ($participant) {
                $participant->update([
                    'validation_status' => ValidationStatus::VALIDATED,
                    'auto_validated_at' => now(),
                ]);

                $this->distributePrize($participant);

                // Creator still gets bonus (goodwill)
                if ($participant->challenge->creator_id) {
                    $this->awardValidationBonus(
                        $participant->challenge,
                        $participant->challenge->creator
                    );
                }

                // Dispatch event
                \App\Events\SuperChallengeAutoValidated::dispatch($participant);
            });
        }
    }

    /**
     * Expire pending nudges after 72 hours
     */
    public function expirePendingNudges(): void
    {
        SuperChallengeNudge::needingExpiration()
            ->update([
                'response' => NudgeResponse::EXPIRED,
                'responded_at' => now(),
            ]);
    }
}
