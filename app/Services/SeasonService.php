<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\GroupSeason;
use App\Models\User;
use App\Models\Wager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeasonService
{
    /**
     * Create a new season for a group
     *
     * @param Group $group
     * @param \Carbon\Carbon|null $endsAt Optional season end date
     * @return GroupSeason
     */
    public function createSeason(Group $group, ?\Carbon\Carbon $endsAt = null): GroupSeason
    {
        return DB::transaction(function () use ($group, $endsAt) {
            // Deactivate current season if exists
            if ($group->currentSeason) {
                $group->currentSeason->update(['is_active' => false]);
            }

            // Get next season number
            $nextSeasonNumber = $group->seasons()->max('season_number') + 1 ?: 1;

            // Create new season
            $season = GroupSeason::create([
                'group_id' => $group->id,
                'season_number' => $nextSeasonNumber,
                'started_at' => now(),
                'is_active' => true,
            ]);

            // Update group references
            $group->update([
                'current_season_id' => $season->id,
                'season_ends_at' => $endsAt,
            ]);

            // Reset all user points to starting balance
            $group->users()->update([
                'points' => $group->starting_balance,
                'points_earned' => 0,
                'points_spent' => 0,
            ]);

            Log::channel('operational')->info('season.created', [
                'group_id' => $group->id,
                'season_id' => $season->id,
                'season_number' => $season->season_number,
                'ends_at' => $endsAt?->toDateTimeString(),
            ]);

            return $season->fresh();
        });
    }

    /**
     * End the current season for a group
     *
     * Calculates final standings, generates highlights, and archives the season.
     *
     * @param Group $group
     * @return GroupSeason
     */
    public function endSeason(Group $group): GroupSeason
    {
        if (!$group->currentSeason) {
            throw new \RuntimeException('No active season to end');
        }

        $season = $group->currentSeason;

        return DB::transaction(function () use ($group, $season) {
            // Calculate final leaderboard
            $leaderboard = $this->calculateFinalLeaderboard($group);

            // Generate season statistics
            $stats = $this->calculateSeasonStats($group, $season);

            // Generate highlights for "Year in Review"
            $highlights = $this->generateHighlights($group, $season);

            // Update season with final data
            $season->update([
                'ended_at' => now(),
                'is_active' => false,
                'final_leaderboard' => $leaderboard,
                'stats' => $stats,
                'highlights' => $highlights,
            ]);

            // Clear group's current season reference
            $group->update([
                'current_season_id' => null,
                'season_ends_at' => null,
            ]);

            Log::channel('operational')->info('season.ended', [
                'group_id' => $group->id,
                'season_id' => $season->id,
                'season_number' => $season->season_number,
                'duration_days' => $season->getDurationInDays(),
                'participants' => count($leaderboard),
            ]);

            return $season->fresh();
        });
    }

    /**
     * Calculate final leaderboard from current standings
     *
     * @param Group $group
     * @return array
     */
    private function calculateFinalLeaderboard(Group $group): array
    {
        $users = $group->users()
            ->orderBy('points', 'desc')
            ->get();

        $rank = 1;
        $leaderboard = [];

        foreach ($users as $user) {
            $leaderboard[] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'points' => $user->pivot->points,
                'points_earned' => $user->pivot->points_earned,
                'points_spent' => $user->pivot->points_spent,
                'rank' => $rank++,
            ];
        }

        return $leaderboard;
    }

    /**
     * Calculate comprehensive season statistics
     *
     * @param Group $group
     * @param GroupSeason $season
     * @return array
     */
    private function calculateSeasonStats(Group $group, GroupSeason $season): array
    {
        $settledWagers = $group->wagers()
            ->where('status', 'settled')
            ->whereBetween('created_at', [$season->started_at, now()])
            ->get();

        return [
            'total_wagers' => $settledWagers->count(),
            'total_participants' => $group->users()->count(),
            'duration_days' => $season->started_at->diffInDays(now()),
            'total_points_wagered' => $settledWagers->sum(function ($wager) {
                return $wager->entries->sum('amount');
            }),
        ];
    }

    /**
     * Generate season highlights for dramatic recap
     *
     * @param Group $group
     * @param GroupSeason $season
     * @return array
     */
    private function generateHighlights(Group $group, GroupSeason $season): array
    {
        $settledWagers = $group->wagers()
            ->where('status', 'settled')
            ->whereBetween('created_at', [$season->started_at, now()])
            ->with('entries.user')
            ->get();

        // Find biggest single win
        $biggestWin = null;
        $biggestWinAmount = 0;

        foreach ($settledWagers as $wager) {
            $winners = $wager->entries->where('result', 'won');
            foreach ($winners as $entry) {
                $potentialWin = $entry->potential_payout ?? 0;
                if ($potentialWin > $biggestWinAmount) {
                    $biggestWinAmount = $potentialWin;
                    $biggestWin = [
                        'user_name' => $entry->user->name,
                        'amount' => $potentialWin,
                        'wager_title' => $wager->title,
                    ];
                }
            }
        }

        // Find most active wagerer (created most wagers)
        $mostActive = $settledWagers
            ->groupBy('creator_id')
            ->map(function ($wagers, $creatorId) use ($group) {
                $user = $group->users()->where('user_id', $creatorId)->first();
                return [
                    'user_name' => $user?->name ?? 'Unknown',
                    'wagers_created' => $wagers->count(),
                ];
            })
            ->sortByDesc('wagers_created')
            ->first();

        // Find most participated wager
        $mostParticipated = $settledWagers
            ->sortByDesc(fn($w) => $w->entries->count())
            ->first();

        return [
            'biggest_win' => $biggestWin,
            'most_active_creator' => $mostActive,
            'most_participated_wager' => $mostParticipated ? [
                'title' => $mostParticipated->title,
                'participants' => $mostParticipated->entries->count(),
            ] : null,
        ];
    }

    /**
     * Check if a season should end based on its end date
     *
     * @param Group $group
     * @return bool
     */
    public function shouldSeasonEnd(Group $group): bool
    {
        if (!$group->season_ends_at || !$group->currentSeason) {
            return false;
        }

        return now()->isAfter($group->season_ends_at);
    }
}
