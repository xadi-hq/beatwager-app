<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;

/**
 * Handle /join command - Join an existing wager
 */
class JoinCommandHandler extends AbstractCommandHandler
{
    public function handle(IncomingMessage $message): void
    {
        $this->messenger->sendMessage(
            OutgoingMessage::text(
                $message->chatId,
                '✋ Join wager coming soon! This will let you join an existing wager.'
            )
        );
    }

    public function getCommand(): string
    {
        return '/join';
    }

    public function getDescription(): string
    {
        return 'Join an existing wager';
    }
}
