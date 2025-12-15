<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BadgeAwarded;
use App\Messaging\Adapters\TelegramAdapter;
use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send notifications when a badge is awarded.
 *
 * - Announces badge in the group chat (with photo)
 * - Sends DM to the user who earned the badge
 * - Marks the UserBadge as notified
 */
class SendBadgeNotification implements ShouldQueue
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

    public function handle(BadgeAwarded $event): void
    {
        $userBadge = $event->userBadge;
        $user = $event->user;
        $badge = $event->badge;
        $group = $event->group;

        // Send group announcement if enabled and group exists
        if ($group !== null && config('badges.notifications.announce_in_group', true)) {
            $this->sendGroupNotification($user, $badge, $group);
        }

        // Send DM to user if enabled
        if (config('badges.notifications.send_dm', true)) {
            $this->sendDirectNotification($user, $badge, $group);
        }

        // Mark as notified
        $userBadge->update(['notified_at' => now()]);
    }

    /**
     * Send badge announcement to the group chat.
     */
    private function sendGroupNotification(User $user, \App\Models\Badge $badge, Group $group): void
    {
        try {
            $emoji = $badge->is_shame ? '😬' : '🎖️';
            $verb = $badge->is_shame ? 'received' : 'earned';

            $caption = sprintf(
                "%s <b>%s</b> just %s the <b>%s</b> badge!\n\n<i>%s</i>",
                $emoji,
                $user->name,
                $verb,
                $badge->name,
                $badge->description
            );

            $this->adapter->sendPhoto(
                chatId: $group->getChatId(),
                photoUrl: $badge->imageUrl,
                caption: $caption,
                parseMode: 'HTML'
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send badge group notification', [
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'group_id' => $group->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send badge notification as a DM to the user.
     */
    private function sendDirectNotification(User $user, \App\Models\Badge $badge, ?Group $group): void
    {
        try {
            // Get user's Telegram service
            $telegramService = $user->getTelegramService();
            if ($telegramService === null) {
                Log::debug('User has no Telegram service for badge DM', [
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                ]);
                return;
            }

            $emoji = $badge->is_shame ? '😬' : '🏆';
            $verb = $badge->is_shame ? 'received' : 'earned';
            $groupContext = $group !== null ? " in {$group->name}" : '';

            $caption = sprintf(
                "%s You %s the <b>%s</b> badge%s!\n\n<i>%s</i>",
                $emoji,
                $verb,
                $badge->name,
                $groupContext,
                $badge->description
            );

            $this->adapter->sendPhoto(
                chatId: $telegramService->platform_user_id,
                photoUrl: $badge->imageUrl,
                caption: $caption,
                parseMode: 'HTML'
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send badge DM notification', [
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(BadgeAwarded $event, \Throwable $exception): void
    {
        Log::error('Failed to send badge notification', [
            'user_id' => $event->user->id,
            'badge_id' => $event->badge->id,
            'group_id' => $event->group?->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
