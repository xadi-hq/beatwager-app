<?php

namespace App\Listeners;

use App\Events\SuperChallengeNudgeSent;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSuperChallengeNudge implements ShouldQueue
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
    public function handle(SuperChallengeNudgeSent $event): void
    {
        $nudge = $event->nudge;
        $user = $nudge->nudgedUser;
        $group = $nudge->group;

        if (!$user || !$group) {
            Log::warning('SuperChallenge nudge missing user or group', [
                'nudge_id' => $nudge->id,
            ]);
            return;
        }

        // Generate DM message to nudged user
        $message = $this->messageService->superChallengeNudge($nudge);

        // Send DM to the nudged user
        $user->sendMessage($message);

        Log::info('Sent SuperChallenge nudge DM', [
            'nudge_id' => $nudge->id,
            'user_id' => $user->id,
            'group_id' => $group->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(SuperChallengeNudgeSent $event, \Throwable $exception): void
    {
        \Log::error('Failed to send SuperChallenge nudge', [
            'nudge_id' => $event->nudge->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
