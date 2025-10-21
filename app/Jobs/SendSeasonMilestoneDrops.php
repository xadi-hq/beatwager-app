<?php

namespace App\Jobs;

use App\Models\AuditEvent;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendSeasonMilestoneDrops implements ShouldQueue
{
    use Queueable;

    /**
     * Season milestone thresholds and drop amounts
     */
    private const MILESTONES = [
        50 => 100,  // 50% progress → 100 points
        75 => 200,  // 75% progress → 200 points
        90 => 500,  // 90% progress → 500 points
    ];

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all groups with seasons enabled and surprise drops enabled
        $groups = Group::where('surprise_drops_enabled', true)
            ->whereNotNull('current_season_id')
            ->whereNotNull('season_ends_at')
            ->get();

        if ($groups->isEmpty()) {
            Log::channel('operational')->info('season_drops.no_eligible_groups');
            return;
        }

        foreach ($groups as $group) {
            $this->processGroupMilestones($group);
        }
    }

    /**
     * Process milestones for a specific group
     */
    private function processGroupMilestones(Group $group): void
    {
        // Calculate season progress percentage
        $seasonProgress = $this->calculateSeasonProgress($group);

        if ($seasonProgress === null) {
            return;
        }

        // Get already triggered milestones
        $triggeredMilestones = $group->season_milestones_triggered ?? [];

        // Check each milestone
        foreach (self::MILESTONES as $threshold => $amount) {
            // Skip if already triggered
            if (in_array((string)$threshold, $triggeredMilestones, true)) {
                continue;
            }

            // Check if we've reached this milestone
            if ($seasonProgress >= $threshold) {
                $this->triggerMilestoneDrop($group, $threshold, $amount);

                // Mark as triggered
                $triggeredMilestones[] = (string)$threshold;
                $group->update([
                    'season_milestones_triggered' => $triggeredMilestones,
                ]);
            }
        }
    }

    /**
     * Calculate season progress as a percentage
     */
    private function calculateSeasonProgress(Group $group): ?float
    {
        if (!$group->season_ends_at) {
            return null;
        }

        // Get the current season
        $season = $group->seasons()->where('id', $group->current_season_id)->first();

        if (!$season || !$season->start_date) {
            return null;
        }

        $start = $season->start_date;
        $end = $group->season_ends_at;
        $now = now();

        // Prevent division by zero
        $totalDuration = $start->diffInSeconds($end);
        if ($totalDuration <= 0) {
            return null;
        }

        // Calculate elapsed time
        $elapsed = $start->diffInSeconds($now);

        // Calculate percentage (capped at 100%)
        return min(100, ($elapsed / $totalDuration) * 100);
    }

    /**
     * Trigger a milestone drop event
     */
    private function triggerMilestoneDrop(Group $group, int $threshold, int $amount): void
    {
        $recipientsCount = 0;

        // Distribute to all group members
        foreach ($group->users as $user) {
            $user->adjustPoints($group, $amount);

            // Create audit event
            AuditEvent::create([
                'event_type' => 'drop.season_milestone',
                'group_id' => $group->id,
                'user_id' => $user->id,
                'metadata' => [
                    'amount' => $amount,
                    'milestone' => "{$threshold}%",
                    'season_id' => $group->current_season_id,
                ],
            ]);

            $recipientsCount++;
        }

        // Send message to group
        $message = $this->generateMilestoneMessage($group, $threshold, $amount);
        $group->sendMessage($message);

        Log::channel('operational')->info('season_drops.milestone_triggered', [
            'group_id' => $group->id,
            'milestone' => "{$threshold}%",
            'amount' => $amount,
            'recipients' => $recipientsCount,
        ]);
    }

    /**
     * Generate milestone drop message
     */
    private function generateMilestoneMessage(Group $group, int $threshold, int $amount): string
    {
        $currencyName = $group->points_currency_name ?? 'points';

        return "🎉 Season Milestone Reached!\n\n"
            . "We're {$threshold}% through the season! 🏆\n"
            . "Everyone receives {$amount} {$currencyName} as a surprise drop!\n\n"
            . "Keep up the great work! 💪";
    }
}
