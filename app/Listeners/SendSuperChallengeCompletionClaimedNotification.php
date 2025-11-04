<?php

namespace App\Listeners;

use App\Events\SuperChallengeCompletionClaimed;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSuperChallengeCompletionClaimedNotification implements ShouldQueue
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
    public function handle(SuperChallengeCompletionClaimed $event): void
    {
        $participant = $event->participant;
        $challenge = $participant->challenge;
        $group = $challenge->group;
        $creator = $challenge->creator;

        if (!$group || !$creator) {
            Log::warning('SuperChallenge completion claim missing group or creator', [
                'participant_id' => $participant->id,
            ]);
            return;
        }

        // Generate DM to creator requesting validation
        $message = $this->messageService->superChallengeCompletionClaimed($participant);

        // Send DM to creator
        $creator->sendMessage($message);

        Log::info('Sent SuperChallenge completion claim notification to creator', [
            'participant_id' => $participant->id,
            'challenge_id' => $challenge->id,
            'creator_id' => $creator->id,
            'user_id' => $participant->user->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(SuperChallengeCompletionClaimed $event, \Throwable $exception): void
    {
        \Log::error('Failed to send SuperChallenge completion claim notification', [
            'participant_id' => $event->participant->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
