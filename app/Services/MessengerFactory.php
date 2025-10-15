<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MessengerInterface;
use App\Models\Group;
use App\Services\Messengers\TelegramMessenger;

/**
 * Factory for resolving the appropriate messenger based on group platform
 */
class MessengerFactory
{
    /**
     * Get the appropriate messenger for a group's platform
     *
     * @param Group $group
     * @return MessengerInterface
     * @throws \Exception
     */
    public static function for(Group $group): MessengerInterface
    {
        return match($group->platform) {
            'telegram' => app(TelegramMessenger::class),
            // 'slack' => app(SlackMessenger::class),      // Future
            // 'discord' => app(DiscordMessenger::class),  // Future
            default => throw new \Exception("Unsupported messenger platform: {$group->platform}"),
        };
    }
}