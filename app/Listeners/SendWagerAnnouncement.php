<?php

namespace App\Listeners;

use App\Events\WagerCreated;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWagerAnnouncement implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
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
    public function handle(WagerCreated $event): void
    {
        $wager = $event->wager;
        $group = $wager->group;

        // Skip if no group context
        if (!$group) {
            return;
        }

        // Generate message (includes LLM call if configured)
        $message = $this->messageService->wagerAnnouncement($wager);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(WagerCreated $event, \Throwable $exception): void
    {
        \Log::error('Failed to send wager announcement', [
            'wager_id' => $event->wager->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
