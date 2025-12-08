<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EliminationChallengeResolved;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEliminationResolvedAnnouncement implements ShouldQueue
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
    public function handle(EliminationChallengeResolved $event): void
    {
        $challenge = $event->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Elimination resolved but no group found', ['challenge_id' => $challenge->id]);
            return;
        }

        $message = $this->messageService->eliminationChallengeResolved($challenge, $event->survivors);
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(EliminationChallengeResolved $event, \Throwable $exception): void
    {
        Log::error('Failed to send elimination resolved announcement', [
            'challenge_id' => $event->challenge->id,
            'survivor_count' => $event->survivors->count(),
            'error' => $exception->getMessage(),
        ]);
    }
}
