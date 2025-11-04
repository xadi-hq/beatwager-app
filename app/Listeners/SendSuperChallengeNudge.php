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

        // Get user's Telegram service to send DM
        $telegramService = $user->getTelegramService();
        if (!$telegramService) {
            Log::warning('User has no Telegram service configured', [
                'nudge_id' => $nudge->id,
                'user_id' => $user->id,
            ]);
            return;
        }

        // Generate DM message to nudged user
        $message = $this->messageService->superChallengeNudge($nudge);

        // Get messenger adapter for sending DMs
        $messengerBridge = \App\Services\MessengerFactory::for($group);
        $adapter = $messengerBridge->getAdapter();

        // Send DM to the nudged user via their Telegram ID
        try {
            $adapter->sendDirectMessage(
                $telegramService->platform_user_id,
                \App\Messaging\DTOs\OutgoingMessage::withButtons(
                    $group->getChatId(),
                    $message->getFormattedContent(),
                    $message->buttons
                )
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send SuperChallenge nudge DM', [
                'nudge_id' => $nudge->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

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
