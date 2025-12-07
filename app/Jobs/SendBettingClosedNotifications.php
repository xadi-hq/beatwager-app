<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\WagerBettingClosed;
use App\Models\Wager;
use App\Services\MessageTrackingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Find wagers where betting just closed and dispatch reveal notifications
 *
 * Runs every 5 minutes to detect wagers that have passed their betting deadline
 * but haven't yet received a betting closed notification.
 */
class SendBettingClosedNotifications implements ShouldQueue
{
    use Queueable;

    /**
     * Minimum participants required to send notification
     */
    private const MIN_PARTICIPANTS = 2;

    public function handle(MessageTrackingService $trackingService): void
    {
        Log::info('SendBettingClosedNotifications: Starting job');

        // Find wagers where betting just closed:
        // - Status is still 'open' (not yet settled)
        // - Betting deadline has passed
        // - Has at least 2 participants (something to reveal)
        // - Hasn't already received a betting.closed notification
        $closedWagers = Wager::where('status', 'open')
            ->where('betting_closes_at', '<', now())
            ->where('participants_count', '>=', self::MIN_PARTICIPANTS)
            ->with(['group', 'entries.user'])
            ->get();

        Log::info('SendBettingClosedNotifications: Found wagers with closed betting', [
            'count' => $closedWagers->count(),
        ]);

        $dispatchedCount = 0;
        $skippedCount = 0;

        foreach ($closedWagers as $wager) {
            $group = $wager->group;

            if (!$group) {
                $skippedCount++;
                continue;
            }

            // Check if we already sent a betting.closed notification for this wager
            if (!$trackingService->canSendMessage(
                $group,
                'betting.closed',
                'wager',
                $wager->id
            )) {
                $skippedCount++;
                Log::debug('SendBettingClosedNotifications: Skipped (already notified)', [
                    'wager_id' => $wager->id,
                ]);
                continue;
            }

            // Record that we're sending this notification (prevents duplicates)
            $trackingService->recordMessage(
                $group,
                'betting.closed',
                "Betting closed for wager: {$wager->title}",
                'wager',
                $wager->id,
                [
                    'participant_count' => $wager->participants_count,
                    'betting_closed_at' => $wager->betting_closes_at->toIso8601String(),
                ]
            );

            // Dispatch the event - listener will check participation rate
            WagerBettingClosed::dispatch($wager);
            $dispatchedCount++;

            Log::info('SendBettingClosedNotifications: Dispatched event', [
                'wager_id' => $wager->id,
                'wager_title' => $wager->title,
            ]);
        }

        Log::info('SendBettingClosedNotifications: Job completed', [
            'total_found' => $closedWagers->count(),
            'dispatched' => $dispatchedCount,
            'skipped' => $skippedCount,
        ]);
    }
}
