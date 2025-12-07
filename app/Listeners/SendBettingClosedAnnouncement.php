<?php

namespace App\Listeners;

use App\Events\WagerBettingClosed;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBettingClosedAnnouncement implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 5;

    /**
     * Minimum participation rate to trigger the announcement (50%)
     */
    private const MIN_PARTICIPATION_RATE = 0.5;

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(WagerBettingClosed $event): void
    {
        $wager = $event->wager;
        $group = $wager->group;

        if (!$group) {
            Log::warning('WagerBettingClosed but no group found', ['wager_id' => $wager->id]);
            return;
        }

        // Check participation rate - only announce for high engagement wagers
        $groupMemberCount = $group->users()->count();
        $participantCount = $wager->participants_count;

        if ($groupMemberCount === 0) {
            Log::debug('WagerBettingClosed: Skipped (no group members)', [
                'wager_id' => $wager->id,
            ]);
            return;
        }

        $participationRate = $participantCount / $groupMemberCount;

        if ($participationRate < self::MIN_PARTICIPATION_RATE) {
            Log::debug('WagerBettingClosed: Skipped (low participation)', [
                'wager_id' => $wager->id,
                'participation_rate' => round($participationRate * 100) . '%',
                'participants' => $participantCount,
                'group_members' => $groupMemberCount,
            ]);
            return;
        }

        // Load entries with users for the reveal
        $entries = $wager->entries()->with('user')->get();

        // Generate and send the betting closed message with bet reveal
        $message = $this->messageService->bettingClosedReveal($wager, $entries);
        $group->sendMessage($message);

        Log::info('WagerBettingClosed: Sent reveal announcement', [
            'wager_id' => $wager->id,
            'wager_title' => $wager->title,
            'group_id' => $group->id,
            'participation_rate' => round($participationRate * 100) . '%',
            'participants' => $participantCount,
        ]);
    }
}
