<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ChallengeType;
use App\Enums\EliminationMode;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientPointsException;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EliminationChallengeService
{
    public function __construct(
        private readonly PointService $pointService
    ) {}

    /**
     * Calculate suggested pot based on group economy (10% of total currency)
     */
    public function calculateSuggestedPot(Group $group): int
    {
        $totalCurrency = $this->getTotalGroupCurrency($group);
        return (int) round($totalCurrency * 0.10);
    }

    /**
     * Calculate buy-in based on pot and group size (50% of fair share)
     */
    public function calculateBuyIn(int $pot, int $groupSize): int
    {
        if ($groupSize === 0) {
            return 0;
        }

        return (int) round(($pot / $groupSize) * 0.5);
    }

    /**
     * Get total currency available in the group
     */
    public function getTotalGroupCurrency(Group $group): int
    {
        return (int) DB::table('group_user')
            ->where('group_id', $group->id)
            ->sum('points');
    }

    /**
     * Create an elimination challenge
     */
    public function createChallenge(
        Group $group,
        User $creator,
        string $name,
        string $eliminationTrigger,
        EliminationMode $mode,
        ?Carbon $deadline,
        int $pot,
        ?Carbon $tapInDeadline = null,
        int $minParticipants = 3
    ): Challenge {
        return DB::transaction(function () use (
            $group, $creator, $name, $eliminationTrigger, $mode,
            $deadline, $pot, $tapInDeadline, $minParticipants
        ) {
            $groupSize = $group->users()->count();
            $buyIn = $this->calculateBuyIn($pot, $groupSize);

            $challenge = Challenge::create([
                'id' => Str::uuid(),
                'type' => ChallengeType::ELIMINATION_CHALLENGE,
                'group_id' => $group->id,
                'creator_id' => $creator->id,
                'description' => $name,
                'elimination_trigger' => $eliminationTrigger,
                'elimination_mode' => $mode,
                'point_pot' => $pot,
                'buy_in_amount' => $buyIn,
                'tap_in_deadline' => $tapInDeadline,
                'completion_deadline' => $deadline,
                'min_participants' => $minParticipants,
                'status' => 'open',
            ]);

            // Dispatch event to announce in group
            \App\Events\EliminationChallengeCreated::dispatch($challenge);

            return $challenge->load(['group', 'creator']);
        });
    }

    /**
     * User taps in to challenge
     */
    public function tapIn(Challenge $challenge, User $user): ChallengeParticipant
    {
        return DB::transaction(function () use ($challenge, $user) {
            // Validate
            if (!$challenge->isEliminationChallenge()) {
                throw new \InvalidArgumentException('This is not an Elimination Challenge');
            }

            if (!$challenge->isTapInOpen()) {
                throw new \InvalidArgumentException('Tap-in period has closed');
            }

            // Check if already tapped in
            $existing = ChallengeParticipant::where('challenge_id', $challenge->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                throw new \InvalidArgumentException('You have already tapped in to this challenge');
            }

            // Ensure group is loaded
            $group = $challenge->group;
            if (!$group) {
                throw new \RuntimeException('Challenge group not found');
            }

            // Check user has enough points for buy-in
            $balance = $this->pointService->getBalance($user, $group);
            if ($balance < $challenge->buy_in_amount) {
                throw new InsufficientPointsException($challenge->buy_in_amount, $balance);
            }

            // Deduct buy-in
            $buyInTransaction = $this->pointService->deductPoints(
                $user,
                $group,
                $challenge->buy_in_amount,
                TransactionType::EliminationBuyIn->value,
                $challenge
            );

            // Create participation record
            $participant = ChallengeParticipant::create([
                'id' => Str::uuid(),
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'accepted_at' => now(),
                'buy_in_transaction_id' => $buyInTransaction->id,
            ]);

            // Dispatch event
            \App\Events\EliminationChallengeTappedIn::dispatch($challenge, $user);

            // Check if minimum participants reached (for activation message)
            if ($challenge->participants()->count() === $challenge->min_participants) {
                \App\Events\EliminationChallengeActivated::dispatch($challenge);
            }

            return $participant->load(['challenge', 'user']);
        });
    }

    /**
     * User taps out (eliminated)
     */
    public function tapOut(
        Challenge $challenge,
        User $user,
        ?string $eliminationNote = null
    ): ChallengeParticipant {
        return DB::transaction(function () use ($challenge, $user, $eliminationNote) {
            $participant = ChallengeParticipant::where('challenge_id', $challenge->id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($participant->isEliminated()) {
                throw new \InvalidArgumentException('You have already been eliminated');
            }

            if (!$challenge->isOpen()) {
                throw new \InvalidArgumentException('Challenge is no longer active');
            }

            // Mark as eliminated (buy-in is forfeited)
            $participant->update([
                'eliminated_at' => now(),
                'elimination_note' => $eliminationNote,
            ]);

            // Dispatch event
            \App\Events\EliminationChallengeTappedOut::dispatch($participant);

            // Check if challenge should resolve (last man standing)
            if ($challenge->shouldResolve()) {
                $this->resolve($challenge);
            } else {
                // Check for milestone (50% eliminated with 3+ survivors)
                $this->checkMilestone($challenge);
            }

            return $participant->fresh()->load(['challenge', 'user']);
        });
    }

    /**
     * Cancel challenge and refund all buy-ins
     */
    public function cancel(Challenge $challenge, ?User $cancelledBy = null): void
    {
        DB::transaction(function () use ($challenge, $cancelledBy) {
            if (!$challenge->isOpen()) {
                throw new \InvalidArgumentException('Only open challenges can be cancelled');
            }

            // Ensure group is loaded
            $group = $challenge->group;
            if (!$group) {
                throw new \RuntimeException('Challenge group not found');
            }

            // Refund all participants
            /** @var Collection<int, ChallengeParticipant> $participants */
            $participants = $challenge->participants()->with('user')->get();

            foreach ($participants as $participant) {
                if ($participant->buy_in_transaction_id && $participant->user) {
                    $this->pointService->awardPoints(
                        $participant->user,
                        $group,
                        $challenge->buy_in_amount,
                        TransactionType::EliminationBuyInRefund->value,
                        $challenge
                    );
                }
            }

            // Update challenge status
            $challenge->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by_id' => $cancelledBy?->id,
            ]);

            // Dispatch event
            \App\Events\EliminationChallengeCancelled::dispatch($challenge);
        });
    }

    /**
     * Resolve challenge and distribute pot to survivors
     */
    public function resolve(Challenge $challenge): void
    {
        DB::transaction(function () use ($challenge) {
            if (!$challenge->isOpen()) {
                throw new \InvalidArgumentException('Challenge is not open');
            }

            $survivors = $challenge->getSurvivors();
            $survivorCount = $survivors->count();

            if ($survivorCount === 0) {
                // No survivors - mark as failed, no points distributed
                $challenge->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => 'All participants were eliminated',
                ]);

                \App\Events\EliminationChallengeResolved::dispatch($challenge, collect());
                return;
            }

            // Ensure group is loaded
            $group = $challenge->group;
            if (!$group) {
                throw new \RuntimeException('Challenge group not found');
            }

            // Calculate payout per survivor
            $payoutPerSurvivor = (int) floor($challenge->point_pot / $survivorCount);

            // Calculate system contribution needed
            $totalBuyIns = $challenge->participants()->count() * $challenge->buy_in_amount;
            $systemContribution = max(0, $challenge->point_pot - $totalBuyIns);

            // Distribute to survivors
            /** @var ChallengeParticipant $participant */
            foreach ($survivors as $participant) {
                if (!$participant->user) {
                    continue;
                }

                $transaction = $this->pointService->awardPoints(
                    $participant->user,
                    $group,
                    $payoutPerSurvivor,
                    TransactionType::EliminationPrize->value,
                    $challenge
                );

                $participant->update([
                    'prize_transaction_id' => $transaction->id,
                ]);
            }

            // Mark challenge as completed
            $challenge->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Dispatch event
            \App\Events\EliminationChallengeResolved::dispatch($challenge, $survivors);
        });
    }

    /**
     * Check for milestone messages (50% eliminated, 2 remaining)
     */
    private function checkMilestone(Challenge $challenge): void
    {
        $totalParticipants = $challenge->participants()->count();
        $survivorCount = $challenge->getSurvivorCount();
        $eliminatedCount = $challenge->getEliminatedCount();

        /** @var array<int, string> $milestonesSent */
        $milestonesSent = $challenge->milestones_sent ?? [];

        // 2 remaining - trigger if not already sent
        if ($survivorCount === 2 && !in_array('final_two', $milestonesSent, true)) {
            \App\Events\EliminationChallengeMilestone::dispatch($challenge, 'final_two');
            $milestonesSent[] = 'final_two';
            $challenge->update(['milestones_sent' => $milestonesSent]);
            return;
        }

        // 50% eliminated - only if 3+ survivors remain and not already sent
        if ($survivorCount >= 3
            && $eliminatedCount >= ($totalParticipants / 2)
            && !in_array('half_eliminated', $milestonesSent, true)
        ) {
            \App\Events\EliminationChallengeMilestone::dispatch($challenge, 'half_eliminated');
            $milestonesSent[] = 'half_eliminated';
            $challenge->update(['milestones_sent' => $milestonesSent]);
        }
    }

    /**
     * Process challenges that have reached their deadline
     */
    public function processDeadlines(): int
    {
        $challenges = Challenge::needingResolution()->get();
        $count = 0;

        foreach ($challenges as $challenge) {
            try {
                $this->resolve($challenge);
                $count++;
            } catch (\Exception $e) {
                \Log::error("Failed to resolve elimination challenge {$challenge->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Process challenges that should auto-cancel (< min participants after tap-in deadline)
     */
    public function processAutoCancels(): int
    {
        $challenges = Challenge::needingAutoCancel()->get();
        $count = 0;

        foreach ($challenges as $challenge) {
            try {
                $this->cancel($challenge);
                $count++;
            } catch (\Exception $e) {
                \Log::error("Failed to auto-cancel elimination challenge {$challenge->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Get challenges needing countdown messages
     *
     * @return \Illuminate\Support\Collection<int, Challenge>
     */
    public function getChallengesNeedingCountdown(): \Illuminate\Support\Collection
    {
        return Challenge::activeEliminationChallenges()
            ->whereNotNull('completion_deadline')
            ->get()
            ->filter(function ($challenge) {
                $hoursLeft = now()->diffInHours($challenge->completion_deadline, false);

                // Countdown triggers: 168h (7d), 48h, 24h, 6h, 1h
                return in_array($hoursLeft, [168, 48, 24, 6, 1]);
            });
    }

    /**
     * Get context for LLM messaging
     *
     * @return array{
     *     challenge_name: string|null,
     *     elimination_trigger: string|null,
     *     mode: string|null,
     *     deadline: string|null,
     *     days_remaining: int|float|null,
     *     hours_remaining: int|float|null,
     *     total_pot: int|null,
     *     pot_per_survivor: int,
     *     survivor_count: int,
     *     eliminated_count: int,
     *     elimination_percentage: float,
     *     survivors: array<int, array{name: string, days_survived: int}>,
     *     eliminated: array<int, array{name: string, eliminated_at: string|null, days_survived: int, note: string|null}>
     * }
     */
    public function getMessageContext(Challenge $challenge): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ChallengeParticipant> $survivors */
        $survivors = $challenge->getSurvivors()->load('user');

        /** @var \Illuminate\Database\Eloquent\Collection<int, ChallengeParticipant> $eliminated */
        $eliminated = $challenge->participants()
            ->whereNotNull('eliminated_at')
            ->with('user')
            ->orderBy('eliminated_at')
            ->get();

        $deadline = $challenge->completion_deadline;
        $mode = $challenge->elimination_mode;

        /** @var array<int, array{name: string, days_survived: int}> $survivorData */
        $survivorData = $survivors->map(function (ChallengeParticipant $participant): array {
            return [
                'name' => $participant->user->display_name ?? $participant->user->name ?? 'Unknown',
                'days_survived' => $participant->getDaysSurvived(),
            ];
        })->values()->all();

        /** @var array<int, array{name: string, eliminated_at: string|null, days_survived: int, note: string|null}> $eliminatedData */
        $eliminatedData = $eliminated->map(function (ChallengeParticipant $participant): array {
            return [
                'name' => $participant->user->display_name ?? $participant->user->name ?? 'Unknown',
                'eliminated_at' => $participant->eliminated_at?->toDateTimeString(),
                'days_survived' => $participant->getDaysSurvived(),
                'note' => $participant->elimination_note,
            ];
        })->values()->all();

        return [
            'challenge_name' => $challenge->description,
            'elimination_trigger' => $challenge->elimination_trigger,
            'mode' => $mode?->value,
            'deadline' => $deadline?->toDateTimeString(),
            'days_remaining' => $deadline?->diffInDays(now()),
            'hours_remaining' => $deadline?->diffInHours(now()),
            'total_pot' => $challenge->point_pot,
            'pot_per_survivor' => $challenge->getPotPerSurvivor(),
            'survivor_count' => $survivors->count(),
            'eliminated_count' => $eliminated->count(),
            'elimination_percentage' => round($challenge->getEliminationPercentage(), 1),
            'survivors' => $survivorData,
            'eliminated' => $eliminatedData,
        ];
    }
}
