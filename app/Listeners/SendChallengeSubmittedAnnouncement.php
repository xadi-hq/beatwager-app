<?php

namespace App\Listeners;

use App\Events\ChallengeSubmitted;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendChallengeSubmittedAnnouncement implements ShouldQueue
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
    public function handle(ChallengeSubmitted $event): void
    {
        $challenge = $event->challenge;
        $submitter = $event->submitter;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Challenge submitted but no group found', ['challenge_id' => $challenge->id]);
            return;
        }

        // Generate message
        $message = $this->messageService->challengeSubmitted($challenge, $submitter);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(ChallengeSubmitted $event, \Throwable $exception): void
    {
        \Log::error('Failed to send challenge submitted announcement', [
            'challenge_id' => $event->challenge->id,
            'submitter_id' => $event->submitter->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
