<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;

/**
 * Handle unknown commands - Provide helpful feedback
 */
class UnknownCommandHandler extends AbstractCommandHandler
{
    public function handle(IncomingMessage $message): void
    {
        $unknownMessage = "❓ Unknown command: {$message->command}\n\n";
        $unknownMessage .= "Available commands:\n";
        $unknownMessage .= "/newwager - Create a new wager\n";
        $unknownMessage .= "/newevent - Create a group event\n";
        $unknownMessage .= "/newchallenge - Create a 1-on-1 challenge\n";
        $unknownMessage .= "/join - Join an existing wager\n";
        $unknownMessage .= "/mywagers - View your active wagers\n";
        $unknownMessage .= "/balance - Check your points balance\n";
        $unknownMessage .= "/leaderboard - View group leaderboard\n";
        $unknownMessage .= "/help - Show detailed help\n\n";
        $unknownMessage .= "Type /help for more information!";

        $this->messenger->sendMessage(
            OutgoingMessage::text($message->chatId, $unknownMessage)
        );
    }

    public function getCommand(): string
    {
        return 'unknown';
    }

    public function getDescription(): string
    {
        return 'Handle unknown commands';
    }
}
