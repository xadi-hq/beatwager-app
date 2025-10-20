<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Wager;
use App\Services\MessageService;
use App\Services\MessageTrackingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Send engagement prompts for stale wagers (low participation after 24+ hours)
 *
 * Runs hourly to detect open wagers with 0-1 participants that have been open for 24+ hours
 */
class SendEngagementPrompts implements ShouldQueue
{
    use Queueable;

    /**
     * Minimum hours a wager must be open before sending engagement prompt
     */
    private const MIN_HOURS_OPEN = 24;

    /**
     * Maximum participants to consider wager "stale"
     */
    private const MAX_PARTICIPANTS = 1;

    public function handle(
        MessageService $messageService,
        MessageTrackingService $trackingService
    ): void {
        Log::info('SendEngagementPrompts: Starting job');

        // Find wagers with low engagement (0-1 participants after 24 hours)
        $staleWagers = Wager::where('status', 'open')
            ->where('created_at', '<=', now()->subHours(self::MIN_HOURS_OPEN))
            ->where('participants_count', '<=', self::MAX_PARTICIPANTS)
            ->with(['group', 'creator'])
            ->get();

        Log::info('SendEngagementPrompts: Found stale wagers', [
            'count' => $staleWagers->count(),
        ]);

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($staleWagers as $wager) {
            $group = $wager->group;

            // Check if group has engagement prompts enabled
            if (!($group->notification_preferences['engagement_prompts'] ?? true)) {
                $skippedCount++;
                Log::debug('SendEngagementPrompts: Skipped (disabled)', [
                    'wager_id' => $wager->id,
                    'group_id' => $group->id,
                ]);
                continue;
            }

            // Check if we can send a prompt (anti-spam)
            if (!$trackingService->canSendMessage(
                $group,
                'engagement.prompt',
                'wager',
                $wager->id
            )) {
                $skippedCount++;
                Log::debug('SendEngagementPrompts: Skipped (anti-spam)', [
                    'wager_id' => $wager->id,
                    'group_id' => $group->id,
                ]);
                continue;
            }

            try {
                // Generate and send engagement message
                $message = $messageService->engagementPrompt($wager);
                $group->sendMessage($message);

                // Record that we sent it
                $trackingService->recordMessage(
                    $group,
                    'engagement.prompt',
                    "Engagement prompt for wager: {$wager->title}",
                    'wager',
                    $wager->id,
                    [
                        'participant_count' => $wager->participants_count,
                        'hours_since_created' => $wager->created_at->diffInHours(now()),
                    ]
                );

                $sentCount++;
                Log::info('SendEngagementPrompts: Sent prompt', [
                    'wager_id' => $wager->id,
                    'wager_title' => $wager->title,
                    'group_id' => $group->id,
                    'participant_count' => $wager->participants_count,
                ]);
            } catch (\Exception $e) {
                Log::error('SendEngagementPrompts: Failed to send prompt', [
                    'wager_id' => $wager->id,
                    'group_id' => $group->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('SendEngagementPrompts: Job completed', [
            'total_stale' => $staleWagers->count(),
            'sent' => $sentCount,
            'skipped' => $skippedCount,
        ]);
    }
}
