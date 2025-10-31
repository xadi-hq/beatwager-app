<?php

namespace App\Listeners;

use App\Events\ChallengeApproved;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendChallengeApprovedAnnouncement implements ShouldQueue
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
    public function handle(ChallengeApproved $event): void
    {
        $challenge = $event->challenge;
        $approver = $event->approver;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Challenge approved but no group found', ['challenge_id' => $challenge->id]);
            return;
        }

        // Generate message
        $message = $this->messageService->challengeApproved($challenge, $approver);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(ChallengeApproved $event, \Throwable $exception): void
    {
        \Log::error('Failed to send challenge approved announcement', [
            'challenge_id' => $event->challenge->id,
            'approver_id' => $event->approver->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
