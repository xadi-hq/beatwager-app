<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;

/**
 * Handle /leaderboard command - Show group rankings
 */
class LeaderboardCommandHandler extends AbstractCommandHandler
{
    public function handle(IncomingMessage $message): void
    {
        $this->messenger->sendMessage(
            OutgoingMessage::text(
                $message->chatId,
                '🏆 Leaderboard coming soon! This will show the top players in your group.'
            )
        );
    }

    public function getCommand(): string
    {
        return '/leaderboard';
    }

    public function getDescription(): string
    {
        return 'View group rankings and top players';
    }
}
