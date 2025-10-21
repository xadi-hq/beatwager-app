<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Log;

/**
 * Handle /help command - Show help documentation
 */
class HelpCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingMessage $message): void
    {
        $helpMessage = $this->buildHelpMessage();

        // Try to send as DM
        try {
            $this->messenger->sendDirectMessage(
                $message->userId,
                OutgoingMessage::markdown($message->chatId, $helpMessage)
            );

            // If command was in group, acknowledge
            if ($message->isGroupContext()) {
                $username = $message->username ? "@{$message->username}" : $message->firstName;
                $this->messenger->sendMessage(
                    OutgoingMessage::text(
                        $message->chatId,
                        "✅ Help sent to your DM, {$username}!"
                    )
                );
            }
        } catch (\Exception $e) {
            // Fallback: send in chat if DM fails
            Log::warning('Failed to send help DM, sending to chat', [
                'user_id' => $message->userId,
                'error' => $e->getMessage(),
            ]);

            $this->messenger->sendMessage(
                OutgoingMessage::markdown($message->chatId, $helpMessage)
            );
        }
    }

    private function buildHelpMessage(): string
    {
        $helpMessage = "📖 *BeatWager Help*\n\n";
        $helpMessage .= "*Available Commands:*\n\n";
        $helpMessage .= "• `/newwager` - Create a new wager in a group\n";
        $helpMessage .= "• `/wagers` - View recent open wagers in this group\n";
        $helpMessage .= "• `/newevent` - Create a group event with attendance tracking\n";
        $helpMessage .= "• `/events` - View recent open events in this group\n";
        $helpMessage .= "• `/newchallenge` - Create a 1-on-1 challenge\n";
        $helpMessage .= "• `/challenges` - View recent open challenges in this group\n";
        $helpMessage .= "• `/mybets` - View your active wagers\n";
        $helpMessage .= "• `/balance` - Check your points balance\n";
        $helpMessage .= "• `/leaderboard` - View group rankings\n";
        $helpMessage .= "• `/help` - Show this help message\n\n";
        $helpMessage .= "*How it works:*\n";
        $helpMessage .= "1️⃣ Create a wager with `/newwager`\n";
        $helpMessage .= "2️⃣ Friends join with their predictions\n";
        $helpMessage .= "3️⃣ When the event happens, settle the wager\n";
        $helpMessage .= "4️⃣ Winners split the pot proportionally!\n\n";
        $helpMessage .= "*Events:*\n";
        $helpMessage .= "📅 Use `/newevent` to organize meetups with attendance bonuses\n";
        $helpMessage .= "✅ RSVP to events and earn points for showing up!\n\n";
        $helpMessage .= "*Challenges:*\n";
        $helpMessage .= "💪 Use `/newchallenge` to offer points OR request points for a task\n";
        $helpMessage .= "⚡ Someone accepts and completes it to earn (or award) the points!\n\n";

        // Create short URL to help page
        $shortCode = ShortUrl::generateUniqueCode(6);
        ShortUrl::create([
            'code' => $shortCode,
            'target_url' => config('app.url') . '/help',
            'expires_at' => now()->addMonth(),
        ]);
        $shortUrl = url('/l/' . $shortCode);

        $helpMessage .= "📚 Full documentation: {$shortUrl}";

        return $helpMessage;
    }

    public function getCommand(): string
    {
        return '/help';
    }

    public function getDescription(): string
    {
        return 'Show help documentation and available commands';
    }
}
