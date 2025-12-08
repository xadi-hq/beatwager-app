<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EliminationChallengeTappedOut;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEliminationTappedOutAnnouncement implements ShouldQueue
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
    public function handle(EliminationChallengeTappedOut $event): void
    {
        $participant = $event->participant;
        $challenge = $participant->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Elimination tap-out but no group found', [
                'challenge_id' => $challenge->id,
                'participant_id' => $participant->id,
            ]);
            return;
        }

        $message = $this->messageService->eliminationChallengeTappedOut($participant);
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(EliminationChallengeTappedOut $event, \Throwable $exception): void
    {
        Log::error('Failed to send elimination tap-out announcement', [
            'participant_id' => $event->participant->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
