<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Wager;
use App\Models\WagerEntry;
use App\Models\Group;
use Illuminate\Support\Facades\DB;

/**
 * Detects engagement triggers when users join wagers
 *
 * Provides context for LLM to generate engaging join announcements
 */
class EngagementTriggerService
{
    /**
     * Detect all applicable triggers for a wager join
     *
     * @return array Trigger flags and context data
     */
    public function detectTriggers(WagerEntry $entry, Wager $wager, User $user, Group $group): array
    {
        return [
            // Position triggers
            'is_first' => $this->isFirstToJoin($wager),
            'is_leader' => $this->isLeader($user, $group),
            'is_underdog' => $this->isUnderdog($user, $group),

            // Stakes triggers
            'is_high_stakes' => $this->isHighStakes($entry, $user, $group),
            'stake_percentage' => $this->getStakePercentage($entry, $user, $group),

            // Comeback triggers
            'is_comeback' => $this->isComeback($user, $group),
            'days_inactive' => $this->getDaysInactive($user, $group),

            // Momentum triggers
            'is_contrarian' => $this->isContrarian($entry, $wager),
            'is_bandwagon' => $this->isBandwagon($entry, $wager),
            'side_distribution' => $this->getSideDistribution($wager),

            // Timing triggers
            'is_last_minute' => $this->isLastMinute($wager),
            'is_early_bird' => $this->isEarlyBird($wager),
            'hours_to_deadline' => $this->getHoursToDeadline($wager),

            // Context
            'leaderboard_rank' => $this->getLeaderboardRank($user, $group),
            'total_participants' => $this->getTotalParticipants($group),
        ];
    }

    /**
     * Check if this is the first entry on the wager
     */
    private function isFirstToJoin(Wager $wager): bool
    {
        return $wager->entries()->count() === 1;
    }

    /**
     * Check if user is #1 on leaderboard
     */
    private function isLeader(User $user, Group $group): bool
    {
        $rank = $this->getLeaderboardRank($user, $group);
        return $rank === 1;
    }

    /**
     * Check if user is in bottom 25% of active participants
     */
    private function isUnderdog(User $user, Group $group): bool
    {
        $rank = $this->getLeaderboardRank($user, $group);
        $totalActive = $this->getTotalParticipants($group);

        if ($totalActive === 0) return false;

        $bottomThreshold = (int) ceil($totalActive * 0.75);
        return $rank >= $bottomThreshold;
    }

    /**
     * Check if wagering >50% of current balance
     */
    private function isHighStakes(WagerEntry $entry, User $user, Group $group): bool
    {
        $balance = $this->getUserBalance($user, $group);

        if ($balance <= 0) return false;

        $percentage = ($entry->points_wagered / $balance) * 100;
        return $percentage >= 50;
    }

    /**
     * Get percentage of balance being wagered
     */
    private function getStakePercentage(WagerEntry $entry, User $user, Group $group): int
    {
        $balance = $this->getUserBalance($user, $group);

        if ($balance <= 0) return 0;

        return (int) round(($entry->points_wagered / $balance) * 100);
    }

    /**
     * Check if user had decay in last 30 days (comeback story)
     */
    private function isComeback(User $user, Group $group): bool
    {
        return DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->where('type', 'decay')
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();
    }

    /**
     * Get days since last wager entry (if inactive)
     */
    private function getDaysInactive(User $user, Group $group): ?int
    {
        $lastEntry = DB::table('wager_entries')
            ->join('wagers', 'wager_entries.wager_id', '=', 'wagers.id')
            ->where('wager_entries.user_id', $user->id)
            ->where('wagers.group_id', $group->id)
            ->orderBy('wager_entries.created_at', 'desc')
            ->skip(1) // Skip the current entry
            ->value('wager_entries.created_at');

        if (!$lastEntry) return null;

        $daysSince = now()->diffInDays($lastEntry);

        // Only return if meaningful inactivity (>7 days)
        return $daysSince > 7 ? $daysSince : null;
    }

    /**
     * Check if joining minority side (contrarian bet)
     */
    private function isContrarian(WagerEntry $entry, Wager $wager): bool
    {
        $distribution = $this->getSideDistribution($wager);

        if (count($distribution) < 2) return false;

        $userSide = $entry->answer_value;
        $userSideCount = $distribution[$userSide] ?? 0;
        $totalCount = array_sum($distribution);

        if ($totalCount === 0) return false;

        $percentage = ($userSideCount / $totalCount) * 100;

        // Contrarian if on side with <40% of votes
        return $percentage < 40;
    }

    /**
     * Check if joining majority side (bandwagon)
     */
    private function isBandwagon(WagerEntry $entry, Wager $wager): bool
    {
        $distribution = $this->getSideDistribution($wager);

        if (count($distribution) < 2) return false;

        $userSide = $entry->answer_value;
        $userSideCount = $distribution[$userSide] ?? 0;
        $totalCount = array_sum($distribution);

        if ($totalCount === 0) return false;

        $percentage = ($userSideCount / $totalCount) * 100;

        // Bandwagon if on side with >60% of votes
        return $percentage > 60;
    }

    /**
     * Get vote distribution by answer
     */
    private function getSideDistribution(Wager $wager): array
    {
        return $wager->entries()
            ->select('answer_value', DB::raw('COUNT(*) as count'))
            ->groupBy('answer_value')
            ->pluck('count', 'answer_value')
            ->toArray();
    }

    /**
     * Check if joining within 24 hours of deadline
     */
    private function isLastMinute(Wager $wager): bool
    {
        $hoursLeft = now()->diffInHours($wager->betting_closes_at, false);
        return $hoursLeft > 0 && $hoursLeft <= 24;
    }

    /**
     * Check if joining within 1 hour of creation
     */
    private function isEarlyBird(Wager $wager): bool
    {
        $hoursSinceCreation = $wager->created_at->diffInHours(now());
        return $hoursSinceCreation <= 1;
    }

    /**
     * Get hours remaining to deadline
     * Uses floor rounding to match diffForHumans() behavior for consistency
     */
    private function getHoursToDeadline(Wager $wager): ?int
    {
        $hoursLeft = now()->diffInHours($wager->betting_closes_at, false);
        return $hoursLeft > 0 ? (int) $hoursLeft : null;
    }

    /**
     * Get user's rank in group leaderboard
     */
    private function getLeaderboardRank(User $user, Group $group): int
    {
        $rank = DB::table('group_user')
            ->select(DB::raw('user_id, RANK() OVER (ORDER BY points DESC) as rank'))
            ->where('group_id', $group->id)
            ->get()
            ->firstWhere('user_id', $user->id);

        return $rank ? (int) $rank->rank : 0;
    }

    /**
     * Get total active participants in group
     */
    private function getTotalParticipants(Group $group): int
    {
        return DB::table('group_user')
            ->where('group_id', $group->id)
            ->where('points', '>', 0)
            ->count();
    }

    /**
     * Get user's current balance in group
     */
    private function getUserBalance(User $user, Group $group): int
    {
        return DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->value('points') ?? 0;
    }
}
