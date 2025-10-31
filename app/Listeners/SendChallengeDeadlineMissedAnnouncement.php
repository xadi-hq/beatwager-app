<?php

namespace App\Listeners;

use App\Events\ChallengeDeadlineMissed;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendChallengeDeadlineMissedAnnouncement implements ShouldQueue
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
    public function handle(ChallengeDeadlineMissed $event): void
    {
        $challenge = $event->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Challenge deadline missed but no group found', ['challenge_id' => $challenge->id]);
            return;
        }

        // Generate message
        $message = $this->messageService->challengeDeadlineMissed($challenge);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(ChallengeDeadlineMissed $event, \Throwable $exception): void
    {
        \Log::error('Failed to send challenge deadline missed announcement', [
            'challenge_id' => $event->challenge->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
