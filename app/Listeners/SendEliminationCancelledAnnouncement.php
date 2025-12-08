<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EliminationChallengeCancelled;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEliminationCancelledAnnouncement implements ShouldQueue
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
    public function handle(EliminationChallengeCancelled $event): void
    {
        $challenge = $event->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Elimination cancelled but no group found', ['challenge_id' => $challenge->id]);
            return;
        }

        // Get cancellation info from the challenge if available
        $cancelledBy = $challenge->cancelledBy ?? null;

        $message = $this->messageService->eliminationChallengeCancelled($challenge, $cancelledBy);
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(EliminationChallengeCancelled $event, \Throwable $exception): void
    {
        Log::error('Failed to send elimination cancelled announcement', [
            'challenge_id' => $event->challenge->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
