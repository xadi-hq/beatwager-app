<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ChallengeType;
use App\Models\Challenge;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Send countdown reminders for elimination challenges approaching their deadline.
 * Milestones: 24h, 12h, 6h, 1h before deadline.
 */
class SendEliminationCountdownReminders implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 300; // 5 minutes

    /**
     * Countdown milestones in hours (descending order for proper selection)
     */
    private const COUNTDOWN_MILESTONES = [24, 12, 6, 1];

    /**
     * Execute the job.
     */
    public function handle(MessageService $messageService): void
    {
        Log::info('SendEliminationCountdownReminders job started');

        // Get active elimination challenges with deadlines
        $challenges = Challenge::query()
            ->where('type', ChallengeType::ELIMINATION_CHALLENGE->value)
            ->where('status', 'open')
            ->whereNotNull('completion_deadline')
            ->where('completion_deadline', '>', now())
            ->get();

        $remindersSent = 0;

        foreach ($challenges as $challenge) {
            // Use a per-challenge lock to prevent race conditions
            $lockKey = "elimination_countdown_{$challenge->id}";

            Cache::lock($lockKey, 60)->get(function () use ($challenge, $messageService, &$remindersSent) {
                try {
                    // Re-fetch the challenge with fresh data inside the lock
                    $freshChallenge = Challenge::find($challenge->id);
                    if (!$freshChallenge || $freshChallenge->status !== 'open') {
                        return;
                    }

                    $hoursRemaining = (int) now()->diffInHours($freshChallenge->completion_deadline, false);

                    // Skip if deadline is past (shouldn't happen due to query, but safety check)
                    if ($hoursRemaining <= 0) {
                        return;
                    }

                    // Find the appropriate milestone
                    $milestone = $this->findNextMilestone($hoursRemaining, $freshChallenge->last_countdown_hours);

                    if ($milestone === null) {
                        return; // No milestone to send
                    }

                    // Send countdown message
                    $message = $messageService->eliminationChallengeCountdown($freshChallenge, $milestone);
                    $freshChallenge->group->sendMessage($message);

                    // Update tracking
                    $freshChallenge->update(['last_countdown_hours' => $milestone]);

                    Log::info("Sent elimination countdown reminder", [
                        'challenge_id' => $freshChallenge->id,
                        'hours_remaining' => $hoursRemaining,
                        'milestone' => $milestone,
                    ]);

                    $remindersSent++;
                } catch (\Exception $e) {
                    Log::error("Failed to send elimination countdown reminder", [
                        'challenge_id' => $challenge->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });
        }

        Log::info("SendEliminationCountdownReminders job completed: {$remindersSent} reminders sent");
    }

    /**
     * Find the next milestone to send based on hours remaining and last sent milestone.
     *
     * @param int $hoursRemaining Hours until deadline
     * @param int|null $lastSent Last milestone that was sent (null if none)
     * @return int|null The milestone to send, or null if none applicable
     */
    private function findNextMilestone(int $hoursRemaining, ?int $lastSent): ?int
    {
        foreach (self::COUNTDOWN_MILESTONES as $milestone) {
            // We've passed this milestone (hours remaining is less than or equal)
            if ($hoursRemaining <= $milestone) {
                // Only send if we haven't sent this milestone yet
                // lastSent being higher means we already sent a larger milestone
                // We need to send milestones as we cross them
                if ($lastSent === null || $lastSent > $milestone) {
                    return $milestone;
                }
            }
        }

        return null;
    }
}
