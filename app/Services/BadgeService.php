<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BadgeCriteriaType;
use App\Enums\DisputeResolution;
use App\Events\BadgeAwarded;
use App\Events\BadgeRevoked;
use App\Models\Badge;
use App\Models\Challenge;
use App\Models\Dispute;
use App\Models\ChallengeParticipant;
use App\Models\DisputeVote;
use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\GroupEventAttendance;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\Wager;
use App\Models\WagerEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing badge awards and revocations.
 *
 * Handles checking badge criteria, awarding badges, and revoking
 * badges when underlying achievements are reversed (e.g., dispute resolution).
 */
class BadgeService
{
    /**
     * Check and award any badges the user qualifies for after an event.
     *
     * @param User $user The user to check
     * @param string $criteriaEvent The event that triggered the check (e.g., 'wager_won')
     * @param Group|null $group The group context (null for global badges)
     * @param array $context Additional context (e.g., triggering wager_id)
     * @return array<UserBadge> Newly awarded badges
     */
    public function checkAndAward(User $user, string $criteriaEvent, ?Group $group = null, array $context = []): array
    {
        $awarded = [];

        try {
            // Get all active badges triggered by this event
            $badges = Badge::active()
                ->byEvent($criteriaEvent)
                ->ordered()
                ->get();

            foreach ($badges as $badge) {
                // Skip if user already has this badge in this context
                if (UserBadge::exists($user->id, $badge->id, $group?->id)) {
                    continue;
                }

                // Check if user meets the criteria
                if ($this->checkCriteria($badge, $user, $group)) {
                    $userBadge = $this->award($user, $badge, $group, $context);
                    if ($userBadge) {
                        $awarded[] = $userBadge;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Badge errors should never block core functionality
            Log::error('Badge check failed', [
                'user_id' => $user->id,
                'criteria_event' => $criteriaEvent,
                'group_id' => $group?->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $awarded;
    }

    /**
     * Check if a user meets the criteria for a specific badge.
     */
    public function checkCriteria(Badge $badge, User $user, ?Group $group): bool
    {
        return match ($badge->criteria_type) {
            BadgeCriteriaType::First => $this->checkFirstCriteria($badge, $user, $group),
            BadgeCriteriaType::Count => $this->checkCountCriteria($badge, $user, $group),
            BadgeCriteriaType::Streak => $this->checkStreakCriteria($badge, $user, $group),
            BadgeCriteriaType::Comparative => $this->checkComparativeCriteria($badge, $user, $group),
        };
    }

    /**
     * Award a badge to a user.
     */
    public function award(User $user, Badge $badge, ?Group $group = null, array $metadata = []): ?UserBadge
    {
        // Double-check uniqueness (defensive)
        if (UserBadge::exists($user->id, $badge->id, $group?->id)) {
            return null;
        }

        $userBadge = UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'group_id' => $group?->id,
            'awarded_at' => now(),
            'metadata' => $metadata,
        ]);

        // Dispatch event for notifications
        event(new BadgeAwarded($userBadge, $user, $badge, $group));

        Log::info('Badge awarded', [
            'user_id' => $user->id,
            'badge_slug' => $badge->slug,
            'group_id' => $group?->id,
        ]);

        return $userBadge;
    }

    /**
     * Revoke a badge from a user.
     */
    public function revoke(UserBadge $userBadge, string $reason): void
    {
        if ($userBadge->isRevoked()) {
            return;
        }

        $userBadge->revoke($reason);

        // Dispatch event for notifications
        event(new BadgeRevoked(
            $userBadge,
            $userBadge->user,
            $userBadge->badge,
            $reason
        ));

        Log::info('Badge revoked', [
            'user_badge_id' => $userBadge->id,
            'user_id' => $userBadge->user_id,
            'badge_slug' => $userBadge->badge->slug,
            'reason' => $reason,
        ]);
    }

    /**
     * Re-check badges after a stat reversal (e.g., dispute resolution).
     *
     * @return array<UserBadge> Revoked badges
     */
    public function recheckAfterReversal(User $user, string $criteriaEvent, ?Group $group = null, string $reason = 'Stats reversed due to dispute'): array
    {
        $revoked = [];

        try {
            // Get user's active badges for this criteria event
            $userBadges = UserBadge::where('user_id', $user->id)
                ->where('group_id', $group?->id)
                ->active()
                ->whereHas('badge', function ($query) use ($criteriaEvent) {
                    $query->where('criteria_event', $criteriaEvent)
                          ->where('is_active', true);
                })
                ->with('badge')
                ->get();

            foreach ($userBadges as $userBadge) {
                $badge = $userBadge->badge;

                // Skip non-revocable badges (e.g., "first" achievements)
                if (!$badge->isRevocable()) {
                    continue;
                }

                // Re-check criteria - if no longer met, revoke
                if (!$this->checkCriteria($badge, $user, $group)) {
                    $this->revoke($userBadge, $reason);
                    $revoked[] = $userBadge;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Badge recheck failed', [
                'user_id' => $user->id,
                'criteria_event' => $criteriaEvent,
                'error' => $e->getMessage(),
            ]);
        }

        return $revoked;
    }

    // =========================================================================
    // Criteria Type Handlers
    // =========================================================================

    /**
     * Check "first" criteria - user has done something at least once.
     */
    private function checkFirstCriteria(Badge $badge, User $user, ?Group $group): bool
    {
        $stat = $this->getUserStat($user, $badge->criteria_event, $group);
        return $stat >= 1;
    }

    /**
     * Check "count" criteria - user has reached a cumulative threshold.
     */
    private function checkCountCriteria(Badge $badge, User $user, ?Group $group): bool
    {
        $stat = $this->getUserStat($user, $badge->criteria_event, $group);
        return $stat >= ($badge->criteria_threshold ?? 0);
    }

    /**
     * Check "streak" criteria - user has a consecutive streak.
     */
    private function checkStreakCriteria(Badge $badge, User $user, ?Group $group): bool
    {
        $streak = $this->getUserStreak($user, $badge->criteria_event, $group);
        return $streak >= ($badge->criteria_threshold ?? 0);
    }

    /**
     * Check "comparative" criteria - user is #1 in a stat.
     */
    private function checkComparativeCriteria(Badge $badge, User $user, ?Group $group): bool
    {
        if (!$group) {
            return false; // Comparative badges require group context
        }

        // If threshold is set, user must meet minimum before being eligible
        if ($badge->criteria_threshold !== null) {
            $userStat = $this->getUserStat($user, $badge->criteria_event, $group);
            if ($userStat < $badge->criteria_threshold) {
                return false;
            }
        }

        $config = $badge->criteria_config ?? [];
        $metric = $config['metric'] ?? 'most';

        if ($metric === 'most') {
            return $this->isTopInGroup($user, $badge->criteria_event, $group);
        }

        return false;
    }

    // =========================================================================
    // Stat Retrieval Methods
    // =========================================================================

    /**
     * Get a user's stat for a specific criteria event.
     */
    public function getUserStat(User $user, string $criteriaEvent, ?Group $group): int
    {
        return match ($criteriaEvent) {
            // Wager stats
            'wager_created' => $this->wagersCreatedCount($user, $group),
            'wager_won' => $this->wagersWonCount($user, $group),
            'wager_lost' => $this->wagersLostCount($user, $group),
            'wager_settled' => $this->wagersSettledCount($user, $group),

            // Event stats
            'event_created' => $this->eventsCreatedCount($user, $group),
            'event_attended' => $this->eventsAttendedCount($user, $group),
            'event_missed' => $this->eventsMissedCount($user, $group),

            // Challenge stats
            'challenge_requested' => $this->challengesRequestedCount($user, $group),
            'challenge_given' => $this->challengesGivenCount($user, $group),
            'super_challenge_won' => $this->superChallengesWonCount($user, $group),
            'elimination_winner' => $this->eliminationWinsCount($user, $group),
            'elimination_tap_out' => $this->eliminationTapOutsCount($user, $group),

            // Dispute stats
            'dispute_judged' => $this->disputesJudgedCount($user, $group),
            'fraud_confirmed' => $this->fraudPenaltiesCount($user, $group),

            default => 0,
        };
    }

    /**
     * Get a user's current streak for a specific criteria event.
     */
    public function getUserStreak(User $user, string $criteriaEvent, ?Group $group): int
    {
        return match ($criteriaEvent) {
            'event_attended' => $this->currentEventStreak($user, $group),
            default => 0,
        };
    }

    // =========================================================================
    // Wager Stat Methods
    // =========================================================================

    private function wagersCreatedCount(User $user, ?Group $group): int
    {
        $query = Wager::where('creator_id', $user->id);

        if ($group) {
            $query->where('group_id', $group->id);
        }

        return $query->count();
    }

    private function wagersWonCount(User $user, ?Group $group): int
    {
        $query = WagerEntry::where('user_id', $user->id)
            ->where('is_winner', true);

        if ($group) {
            $query->where('group_id', $group->id);
        }

        return $query->count();
    }

    private function wagersLostCount(User $user, ?Group $group): int
    {
        $query = WagerEntry::where('user_id', $user->id)
            ->where('is_winner', false)
            ->whereNotNull('result'); // Only count settled wagers

        if ($group) {
            $query->where('group_id', $group->id);
        }

        return $query->count();
    }

    private function wagersSettledCount(User $user, ?Group $group): int
    {
        $query = Wager::where('settler_id', $user->id)
            ->whereNotNull('settled_at');

        if ($group) {
            $query->where('group_id', $group->id);
        }

        return $query->count();
    }

    // =========================================================================
    // Event Stat Methods
    // =========================================================================

    private function eventsCreatedCount(User $user, ?Group $group): int
    {
        $query = GroupEvent::where('created_by_user_id', $user->id);

        if ($group) {
            $query->where('group_id', $group->id);
        }

        return $query->count();
    }

    private function eventsAttendedCount(User $user, ?Group $group): int
    {
        $query = GroupEventAttendance::where('user_id', $user->id)
            ->where('attended', true);

        if ($group) {
            $query->whereHas('event', fn($q) => $q->where('group_id', $group->id));
        }

        return $query->count();
    }

    private function eventsMissedCount(User $user, ?Group $group): int
    {
        $query = GroupEventAttendance::where('user_id', $user->id)
            ->where('attended', false);

        if ($group) {
            $query->whereHas('event', fn($q) => $q->where('group_id', $group->id));
        }

        return $query->count();
    }

    private function currentEventStreak(User $user, ?Group $group): int
    {
        // Get attendance records ordered by actual event date (not record creation)
        $query = GroupEventAttendance::where('user_id', $user->id)
            ->join('group_events', 'group_event_attendance.event_id', '=', 'group_events.id')
            ->orderByDesc('group_events.event_date')
            ->select('group_event_attendance.*');

        if ($group) {
            $query->where('group_events.group_id', $group->id);
        }

        $records = $query->get();

        $streak = 0;
        foreach ($records as $record) {
            if ($record->attended) {
                $streak++;
            } else {
                break; // Streak broken
            }
        }

        return $streak;
    }

    // =========================================================================
    // Challenge Stat Methods
    // =========================================================================

    /**
     * Count challenges where user accepted/took on a challenge.
     *
     * "Request challenge" badge = user asked to take on someone else's challenge offer.
     * The acceptor is "requesting" to do the task for payment.
     */
    private function challengesRequestedCount(User $user, ?Group $group): int
    {
        $query = Challenge::where('acceptor_id', $user->id)
            ->whereIn('status', ['accepted', 'completed', 'failed']);

        if ($group) {
            $query->where('group_id', $group->id);
        }

        return $query->count();
    }

    /**
     * Count challenges where user created a challenge that was completed.
     *
     * "Given challenge" badge = user created a challenge offer that someone completed.
     * The creator "gave" out a task/opportunity.
     */
    private function challengesGivenCount(User $user, ?Group $group): int
    {
        $query = Challenge::where('creator_id', $user->id)
            ->where('status', 'completed');

        if ($group) {
            $query->where('group_id', $group->id);
        }

        return $query->count();
    }

    private function superChallengesWonCount(User $user, ?Group $group): int
    {
        // Count validated participations in super challenges
        $query = ChallengeParticipant::where('user_id', $user->id)
            ->where('validation_status', 'validated')
            ->whereHas('challenge', fn($q) => $q->where('type', 'super_challenge'));

        if ($group) {
            $query->whereHas('challenge', fn($q) => $q->where('group_id', $group->id));
        }

        return $query->count();
    }

    private function eliminationWinsCount(User $user, ?Group $group): int
    {
        // Count elimination challenges where user survived and won
        $query = ChallengeParticipant::where('user_id', $user->id)
            ->whereNull('eliminated_at')
            ->whereHas('challenge', fn($q) => $q
                ->where('type', 'elimination_challenge')
                ->where('status', 'completed')
            );

        if ($group) {
            $query->whereHas('challenge', fn($q) => $q->where('group_id', $group->id));
        }

        return $query->count();
    }

    private function eliminationTapOutsCount(User $user, ?Group $group): int
    {
        // Count times user was eliminated
        $query = ChallengeParticipant::where('user_id', $user->id)
            ->whereNotNull('eliminated_at');

        if ($group) {
            $query->whereHas('challenge', fn($q) => $q->where('group_id', $group->id));
        }

        return $query->count();
    }

    // =========================================================================
    // Dispute Stat Methods
    // =========================================================================

    private function disputesJudgedCount(User $user, ?Group $group): int
    {
        $query = DisputeVote::where('voter_id', $user->id);

        if ($group) {
            $query->whereHas('dispute', fn($q) => $q->where('group_id', $group->id));
        }

        return $query->count();
    }

    private function fraudPenaltiesCount(User $user, ?Group $group): int
    {
        $query = Dispute::where('accused_id', $user->id)
            ->where('resolution', DisputeResolution::FraudConfirmed);

        if ($group) {
            $query->where('group_id', $group->id);
        }

        return $query->count();
    }

    // =========================================================================
    // Comparative Methods
    // =========================================================================

    /**
     * Check if user has the highest stat in their group for a criteria event.
     *
     * Note: In case of ties, ALL tied users qualify for the badge.
     * This is intentional - if two users both have 10 settled wagers and that's
     * the max, they both earn "Wager King."
     */
    private function isTopInGroup(User $user, string $criteriaEvent, Group $group): bool
    {
        $userStat = $this->getUserStat($user, $criteriaEvent, $group);

        if ($userStat === 0) {
            return false;
        }

        // Find the maximum stat among ALL group members (single query)
        $maxStat = $this->getMaxStatInGroup($criteriaEvent, $group);

        // User qualifies if their stat equals the max (handles ties)
        return $userStat >= $maxStat;
    }

    /**
     * Get the maximum stat value for a criteria event among all group members.
     * Uses optimized single-query approach per stat type.
     */
    private function getMaxStatInGroup(string $criteriaEvent, Group $group): int
    {
        $memberIds = $group->users()->pluck('users.id');

        return match ($criteriaEvent) {
            'wager_settled' => Wager::where('group_id', $group->id)
                ->whereIn('settler_id', $memberIds)
                ->whereNotNull('settled_at')
                ->selectRaw('settler_id, COUNT(*) as count')
                ->groupBy('settler_id')
                ->orderByDesc('count')
                ->value('count') ?? 0,

            'wager_won' => WagerEntry::where('group_id', $group->id)
                ->whereIn('user_id', $memberIds)
                ->where('is_winner', true)
                ->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->orderByDesc('count')
                ->value('count') ?? 0,

            'wager_created' => Wager::where('group_id', $group->id)
                ->whereIn('creator_id', $memberIds)
                ->selectRaw('creator_id, COUNT(*) as count')
                ->groupBy('creator_id')
                ->orderByDesc('count')
                ->value('count') ?? 0,

            default => 0,
        };
    }

    // =========================================================================
    // Utility Methods
    // =========================================================================

    /**
     * Get all badges a user has earned (active only).
     */
    public function getUserBadges(User $user, ?Group $group = null): Collection
    {
        $query = UserBadge::where('user_id', $user->id)
            ->active()
            ->with('badge')
            ->orderByDesc('awarded_at');

        if ($group !== null) {
            // Get both group-specific and global badges
            $query->where(function ($q) use ($group) {
                $q->where('group_id', $group->id)
                  ->orWhereNull('group_id');
            });
        }

        return $query->get();
    }

    /**
     * Get badge progress for a user (for UI display).
     */
    public function getBadgeProgress(User $user, Badge $badge, ?Group $group = null): array
    {
        $hasEarned = UserBadge::exists($user->id, $badge->id, $group?->id);

        if ($badge->criteria_type === BadgeCriteriaType::Streak) {
            $current = $this->getUserStreak($user, $badge->criteria_event, $group);
        } else {
            $current = $this->getUserStat($user, $badge->criteria_event, $group);
        }

        $threshold = max(1, $badge->criteria_threshold ?? 1);

        return [
            'badge' => $badge,
            'earned' => $hasEarned,
            'current' => $current,
            'threshold' => $threshold,
            'progress_percent' => min(100, (int) (($current / $threshold) * 100)),
        ];
    }
}
