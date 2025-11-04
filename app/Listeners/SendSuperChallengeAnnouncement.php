<?php

namespace App\Listeners;

use App\Events\SuperChallengeCreated;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSuperChallengeAnnouncement implements ShouldQueue
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
    public function handle(SuperChallengeCreated $event): void
    {
        $challenge = $event->challenge;
        $group = $challenge->group;

        if (!$group) {
            Log::warning('SuperChallenge created but no group found', [
                'challenge_id' => $challenge->id,
            ]);
            return;
        }

        // Generate announcement message
        $message = $this->messageService->superChallengeAnnouncement($challenge);

        // Send to group chat
        $group->sendMessage($message);

        Log::info('Sent SuperChallenge announcement', [
            'challenge_id' => $challenge->id,
            'group_id' => $group->id,
            'description' => $challenge->description,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(SuperChallengeCreated $event, \Throwable $exception): void
    {
        \Log::error('Failed to send SuperChallenge announcement', [
            'challenge_id' => $event->challenge->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
