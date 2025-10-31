<?php

namespace App\Listeners;

use App\Events\ChallengeRejected;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendChallengeRejectedAnnouncement implements ShouldQueue
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
    public function handle(ChallengeRejected $event): void
    {
        $challenge = $event->challenge;
        $rejector = $event->rejector;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Challenge rejected but no group found', ['challenge_id' => $challenge->id]);
            return;
        }

        // Generate message
        $message = $this->messageService->challengeRejected($challenge, $rejector);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(ChallengeRejected $event, \Throwable $exception): void
    {
        \Log::error('Failed to send challenge rejected announcement', [
            'challenge_id' => $event->challenge->id,
            'rejector_id' => $event->rejector->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
