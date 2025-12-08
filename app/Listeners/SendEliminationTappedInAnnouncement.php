<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EliminationChallengeTappedIn;
use App\Models\ChallengeParticipant;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendEliminationTappedInAnnouncement implements ShouldQueue
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
    public function handle(EliminationChallengeTappedIn $event): void
    {
        $challenge = $event->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('Elimination tap-in but no group found', ['challenge_id' => $challenge->id]);
            return;
        }

        // Get the participant record
        /** @var ChallengeParticipant|null $participant */
        $participant = $challenge->participants()
            ->where('user_id', $event->user->id)
            ->first();

        if (!$participant) {
            Log::warning('Elimination tap-in but participant not found', [
                'challenge_id' => $challenge->id,
                'user_id' => $event->user->id,
            ]);
            return;
        }

        $message = $this->messageService->eliminationChallengeTappedIn($participant);
        $group->sendMessage($message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(EliminationChallengeTappedIn $event, \Throwable $exception): void
    {
        Log::error('Failed to send elimination tap-in announcement', [
            'challenge_id' => $event->challenge->id,
            'user_id' => $event->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
