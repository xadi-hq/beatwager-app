<?php

namespace App\Listeners;

use App\Events\SuperChallengeCompletionValidated;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSuperChallengeValidatedNotification implements ShouldQueue
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
    public function handle(SuperChallengeCompletionValidated $event): void
    {
        $participant = $event->participant;
        $approved = $event->approved;
        $challenge = $participant->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('SuperChallenge validation missing group', [
                'participant_id' => $participant->id,
            ]);
            return;
        }

        // Generate validation result message for group chat
        $message = $this->messageService->superChallengeValidated($participant, $approved);

        // Send to group chat
        $group->sendMessage($message);

        Log::info('Sent SuperChallenge validation notification', [
            'participant_id' => $participant->id,
            'challenge_id' => $challenge->id,
            'user_id' => $participant->user->id,
            'approved' => $approved,
            'group_id' => $group->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(SuperChallengeCompletionValidated $event, \Throwable $exception): void
    {
        \Log::error('Failed to send SuperChallenge validation notification', [
            'participant_id' => $event->participant->id,
            'approved' => $event->approved,
            'error' => $exception->getMessage(),
        ]);
    }
}
