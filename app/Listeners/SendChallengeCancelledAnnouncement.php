<?php

namespace App\Listeners;

use App\Events\ChallengeCancelled;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendChallengeCancelledAnnouncement implements ShouldQueue
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
    public function handle(ChallengeCancelled $event): void
    {
        $challenge = $event->challenge;
        $cancelledBy = $event->cancelledBy;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Challenge cancelled but no group found', ['challenge_id' => $challenge->id]);
            return;
        }

        // Generate message
        $message = $this->messageService->challengeCancelled($challenge, $cancelledBy);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(ChallengeCancelled $event, \Throwable $exception): void
    {
        \Log::error('Failed to send challenge cancelled announcement', [
            'challenge_id' => $event->challenge->id,
            'cancelled_by' => $event->cancelledBy->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
