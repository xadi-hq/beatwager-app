<?php

namespace App\Listeners;

use App\Events\EventCancelled;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEventCancelledAnnouncement implements ShouldQueue
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
    public function handle(EventCancelled $event): void
    {
        $groupEvent = $event->event;
        $group = $groupEvent->group;

        if (!$group) {
            Log::warning('Event cancelled but no group found', ['event_id' => $groupEvent->id]);
            return;
        }

        // Generate message
        $message = $this->messageService->eventCancelled($groupEvent);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(EventCancelled $event, \Throwable $exception): void
    {
        \Log::error('Failed to send event cancelled announcement', [
            'event_id' => $event->event->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
