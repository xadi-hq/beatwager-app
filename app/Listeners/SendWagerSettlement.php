<?php

namespace App\Listeners;

use App\Events\WagerSettled;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWagerSettlement implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 5;

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(WagerSettled $event): void
    {
        $wager = $event->wager;
        $group = $wager->group;

        if (!$group) {
            Log::warning('Wager settled but no group found', ['wager_id' => $wager->id]);
            return;
        }

        // Get winners
        $winners = $wager->entries()->where('is_winner', true)->with('user')->get();

        // Generate message (includes LLM call if configured)
        $message = $this->messageService->settlementResult($wager, $winners);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }
}
