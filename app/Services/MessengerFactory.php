<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MessengerInterface;
use App\Messaging\Adapters\TelegramAdapter;
use App\Models\Group;
use App\Services\Messengers\MessengerBridge;

/**
 * Factory for resolving the appropriate messenger based on group platform
 *
 * @deprecated Use dependency injection of MessengerAdapterInterface instead
 * This factory now returns a bridge to the new MessengerAdapterInterface system
 */
class MessengerFactory
{
    /**
     * Get the appropriate messenger for a group's platform
     *
     * Returns a MessengerBridge that adapts the new MessengerAdapterInterface
     * to the old MessengerInterface for backward compatibility.
     *
     * @param Group $group
     * @return MessengerInterface
     * @throws \Exception
     */
    public static function for(Group $group): MessengerInterface
    {
        $adapter = match($group->platform) {
            'telegram' => app(TelegramAdapter::class),
            // 'slack' => app(SlackAdapter::class),      // Future
            // 'discord' => app(DiscordAdapter::class),  // Future
            default => throw new \Exception("Unsupported messenger platform: {$group->platform}"),
        };

        return new MessengerBridge($adapter);
    }
}