<?php

namespace App\Listeners;

use App\Events\EventRsvpUpdated;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRsvpAnnouncement implements ShouldQueue
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
    public function handle(EventRsvpUpdated $event): void
    {
        $groupEvent = $event->event;
        $user = $event->user;
        $response = $event->response;
        $previousResponse = $event->previousResponse;
        $group = $groupEvent->group;

        \Log::info('SendRsvpAnnouncement Listener', [
            'event' => $groupEvent->name,
            'user' => $user->name,
            'response' => $response,
            'previousResponse' => $previousResponse,
            'has_llm' => $group->llm_api_key !== null,
        ]);

        // Generate LLM-powered message based on RSVP response (detects changes)
        $message = $this->messageService->rsvpUpdated($groupEvent, $user, $response, $previousResponse);

        \Log::info('RSVP Message Generated', [
            'content' => $message->content,
            'type' => $message->type->value,
        ]);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }
}
