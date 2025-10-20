<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;

/**
 * Handle /start command - Welcome new users
 */
class StartCommandHandler extends AbstractCommandHandler
{
    public function handle(IncomingMessage $message): void
    {
        $firstName = $message->firstName ?? 'there';

        $welcomeMessage = "👋 Welcome to BeatWager, {$firstName}!\n\n";
        $welcomeMessage .= "I help you create and manage friendly wagers and events with your group.\n\n";
        $welcomeMessage .= "Available commands:\n";
        $welcomeMessage .= "/newwager - Create a new wager\n";
        $welcomeMessage .= "/newevent - Create a group event\n";
        $welcomeMessage .= "/newchallenge - Create a 1-on-1 challenge\n";
        $welcomeMessage .= "/join - Join an existing wager\n";
        $welcomeMessage .= "/mywagers - View your active wagers\n";
        $welcomeMessage .= "/balance - Check your points balance\n";
        $welcomeMessage .= "/leaderboard - View group leaderboard\n";
        $welcomeMessage .= "/help - Show this help message\n\n";
        $welcomeMessage .= "Let's get started! Use /newwager, /newevent, or /newchallenge to begin.";

        $this->messenger->sendMessage(
            OutgoingMessage::text($message->chatId, $welcomeMessage)
        );
    }

    public function getCommand(): string
    {
        return '/start';
    }

    public function getDescription(): string
    {
        return 'Start using the bot and see available commands';
    }
}
