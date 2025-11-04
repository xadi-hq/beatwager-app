<?php

namespace App\Listeners;

use App\Events\SuperChallengeAccepted;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSuperChallengeAcceptedNotification implements ShouldQueue
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
    public function handle(SuperChallengeAccepted $event): void
    {
        $participant = $event->participant;
        $challenge = $participant->challenge;
        $group = $challenge->group;
        $user = $participant->user;

        if (!$group || !$user) {
            Log::warning('SuperChallenge acceptance missing group or user', [
                'participant_id' => $participant->id,
            ]);
            return;
        }

        // Generate acceptance notification for group chat
        $message = $this->messageService->superChallengeAccepted($participant);

        // Send to group chat
        $group->sendMessage($message);

        Log::info('Sent SuperChallenge acceptance notification', [
            'participant_id' => $participant->id,
            'challenge_id' => $challenge->id,
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(SuperChallengeAccepted $event, \Throwable $exception): void
    {
        \Log::error('Failed to send SuperChallenge acceptance notification', [
            'participant_id' => $event->participant->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
