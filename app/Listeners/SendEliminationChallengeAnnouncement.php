<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EliminationChallengeCreated;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEliminationChallengeAnnouncement implements ShouldQueue
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
    public function handle(EliminationChallengeCreated $event): void
    {
        $challenge = $event->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Elimination challenge created but no group found', ['challenge_id' => $challenge->id]);
            return;
        }

        $message = $this->messageService->eliminationChallengeAnnouncement($challenge);
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(EliminationChallengeCreated $event, \Throwable $exception): void
    {
        Log::error('Failed to send elimination challenge announcement', [
            'challenge_id' => $event->challenge->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
