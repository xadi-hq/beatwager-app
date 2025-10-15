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
 * Handle /newwager command - Create a new wager
 */
class NewWagerCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingMessage $message): void
    {
        // Only allow in group chats
        if (!$message->isGroupContext()) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "❌ Please use /newwager in a group chat where you want to create the wager.\n\n" .
                    "I need to know which group the wager is for!"
                )
            );
            return;
        }

        // Get chat info from metadata
        $chat = $message->getChat();
        $username = $message->username ? '@' . $message->username : $message->firstName;
        $botUsername = config('telegram.bot_username', 'WagerBot');

        // Try to send DM to verify user has started chat with bot
        try {
            // Attempt to send a test message
            $this->messenger->sendDirectMessage(
                $message->userId,
                OutgoingMessage::text($message->chatId, '✅ Initializing wager creation...')
            );

            // If we reach here, DM works - generate the actual link
            $fullUrl = $this->messenger->createAuthenticatedUrl(
                'wager.create',
                [
                    'u' => encrypt($message->platform . ':' . $message->userId),
                    'username' => $message->username,
                    'first_name' => $message->firstName,
                    'last_name' => $message->lastName,
                    'chat_id' => $chat->getId(),
                    'chat_type' => $message->getChatType(),
                    'chat_title' => $chat->getTitle(),
                ],
                30 // 30 minutes expiry
            );

            // Create short URL for cleaner message
            $shortCode = ShortUrl::generateUniqueCode(6);
            ShortUrl::create([
                'code' => $shortCode,
                'target_url' => $fullUrl,
                'expires_at' => now()->addMinutes(30),
            ]);
            $shortUrlFull = url('/l/' . $shortCode);

            $chatTitle = $chat->getTitle();
            $dmMessage = "🎲 *Create a new wager for {$chatTitle}*\n\n";
            $dmMessage .= "Click the link to open the creation form:\n";
            $dmMessage .= $shortUrlFull . "\n\n";
            $dmMessage .= "⏱️ Link expires in 30 minutes";

            $this->messenger->sendDirectMessage(
                $message->userId,
                OutgoingMessage::markdown($message->chatId, $dmMessage)
            );

            // Send reaction instead of text message in group to reduce noise
            $this->sendReaction($message, '👍');

        } catch (\Exception $e) {
            // If we can't send DM (user hasn't started bot), ask them to start a chat first
            Log::info('User has not started bot chat, prompting to initialize', [
                'user_id' => $message->userId,
                'error' => $e->getMessage(),
            ]);

            $initMessage = "👋 Hi {$username}! I'd need you to start a chat with me directly at @{$botUsername} first.\n\n";
            $initMessage .= "Just click the link above or search for @{$botUsername} in Telegram and send me /start.\n\n";
            $initMessage .= "Once you've done that, you can use /newwager here and I'll send you the creation link privately!";

            $this->messenger->sendMessage(
                OutgoingMessage::text($message->chatId, $initMessage)
            );
        }
    }

    public function getCommand(): string
    {
        return '/newwager';
    }

    public function getDescription(): string
    {
        return 'Create a new wager in a group';
    }
}
