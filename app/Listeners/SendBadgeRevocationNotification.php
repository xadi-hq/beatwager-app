<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BadgeRevoked;
use App\Messaging\Adapters\TelegramAdapter;
use App\Messaging\DTOs\OutgoingMessage;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send notification when a badge is revoked from a user.
 *
 * Per the plan: DM only (not group), explain reason for revocation.
 */
class SendBadgeRevocationNotification implements ShouldQueue
{
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 5;

    public function __construct(
        private readonly TelegramAdapter $adapter
    ) {}

    public function handle(BadgeRevoked $event): void
    {
        $user = $event->user;
        $badge = $event->badge;
        $reason = $event->reason;

        // Only send DM to user (not group announcement for revocations)
        $this->sendDirectNotification($user, $badge, $reason);
    }

    /**
     * Send badge revocation notification as a DM to the user.
     */
    private function sendDirectNotification(User $user, Badge $badge, string $reason): void
    {
        try {
            // Get user's Telegram service
            $telegramService = $user->getTelegramService();
            if ($telegramService === null) {
                Log::debug('User has no Telegram service for badge revocation DM', [
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                ]);
                return;
            }

            $message = sprintf(
                "😔 Your <b>%s</b> badge has been revoked.\n\n<i>Reason: %s</i>",
                $badge->name,
                $reason
            );

            $this->adapter->sendDirectMessage(
                $telegramService->platform_user_id,
                OutgoingMessage::html(
                    chatId: $telegramService->platform_user_id,
                    text: $message
                )
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send badge revocation DM', [
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(BadgeRevoked $event, \Throwable $exception): void
    {
        Log::error('Failed to send badge revocation notification', [
            'user_id' => $event->user->id,
            'badge_id' => $event->badge->id,
            'reason' => $event->reason,
            'error' => $exception->getMessage(),
        ]);
    }
}
