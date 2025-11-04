<?php

namespace App\Listeners;

use App\Events\SuperChallengeAutoValidated;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSuperChallengeAutoValidatedNotification implements ShouldQueue
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
    public function handle(SuperChallengeAutoValidated $event): void
    {
        $participant = $event->participant;
        $challenge = $participant->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('SuperChallenge auto-validation missing group', [
                'participant_id' => $participant->id,
            ]);
            return;
        }

        // Generate auto-validation message for group chat
        $message = $this->messageService->superChallengeAutoValidated($participant);

        // Send to group chat
        $group->sendMessage($message);

        Log::info('Sent SuperChallenge auto-validation notification', [
            'participant_id' => $participant->id,
            'challenge_id' => $challenge->id,
            'user_id' => $participant->user->id,
            'group_id' => $group->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(SuperChallengeAutoValidated $event, \Throwable $exception): void
    {
        \Log::error('Failed to send SuperChallenge auto-validation notification', [
            'participant_id' => $event->participant->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
