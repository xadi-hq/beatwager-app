<?php

namespace App\Listeners;

use App\Events\EventCreated;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEventAnnouncement implements ShouldQueue
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
    public function handle(EventCreated $event): void
    {
        $groupEvent = $event->event;
        $group = $groupEvent->group;

        if (!$group) {
            Log::warning('Event created but no group found', ['event_id' => $groupEvent->id]);
            return;
        }

        // Generate message (includes LLM call if configured)
        $message = $this->messageService->eventAnnouncement($groupEvent);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }
}
