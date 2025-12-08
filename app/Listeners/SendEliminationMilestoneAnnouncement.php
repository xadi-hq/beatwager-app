<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EliminationChallengeMilestone;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEliminationMilestoneAnnouncement implements ShouldQueue
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
    public function handle(EliminationChallengeMilestone $event): void
    {
        $challenge = $event->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Elimination milestone but no group found', [
                'challenge_id' => $challenge->id,
                'milestone' => $event->milestone,
            ]);
            return;
        }

        $message = $this->messageService->eliminationChallengeMilestone($challenge, $event->milestone);
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(EliminationChallengeMilestone $event, \Throwable $exception): void
    {
        Log::error('Failed to send elimination milestone announcement', [
            'challenge_id' => $event->challenge->id,
            'milestone' => $event->milestone,
            'error' => $exception->getMessage(),
        ]);
    }
}
