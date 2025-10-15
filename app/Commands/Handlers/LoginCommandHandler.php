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
 * Handle /login command - Generate authentication link for webapp
 *
 * Works in both group and private chat contexts, always sends link via DM
 */
class LoginCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingMessage $message): void
    {
        // Determine context for the login (group or private)
        $contextChatId = null;
        $contextChatTitle = null;
        $contextChatType = $message->getChatType();

        // If invoked in group, store group context for after-login redirect
        if ($message->isGroupContext()) {
            $chat = $message->getChat();
            $contextChatId = $chat->getId();
            $contextChatTitle = $chat->getTitle();
        }

        // Generate authentication URL for webapp
        $params = [
            'u' => encrypt($message->platform . ':' . $message->userId),
            'username' => $message->username,
            'first_name' => $message->firstName,
            'last_name' => $message->lastName,
        ];

        // Add context params if invoked from group
        if ($contextChatId !== null) {
            $params['chat_id'] = $contextChatId;
            $params['chat_type'] = $contextChatType;
            $params['chat_title'] = $contextChatTitle;
        }

        $fullUrl = $this->messenger->createAuthenticatedUrl(
            'dashboard.me',
            $params,
            60 // 60 minutes expiry for login links
        );

        // Create short URL
        $shortCode = ShortUrl::generateUniqueCode(6);
        ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $fullUrl,
            'expires_at' => now()->addMinutes(60),
        ]);
        $shortUrlFull = url('/l/' . $shortCode);

        // Always send link via DM
        try {
            $dmMessage = "🔐 *Login to BeatWager*\n\n";
            $dmMessage .= "Click the link below to access the webapp:\n";
            $dmMessage .= $shortUrlFull . "\n\n";
            $dmMessage .= "⏱️ Link expires in 60 minutes\n\n";

            if ($contextChatTitle) {
                $dmMessage .= "📍 Context: {$contextChatTitle}";
            } else {
                $dmMessage .= "You'll be able to view your wagers, challenges, and manage your account.";
            }

            $this->messenger->sendDirectMessage(
                $message->userId,
                OutgoingMessage::markdown($message->chatId, $dmMessage)
            );

            // If invoked in group, react with thumbs up (no text message to reduce noise)
            if ($message->isGroupContext()) {
                $this->sendReaction($message, '👍');
            }

        } catch (\Exception $e) {
            // If we can't send DM, inform user they need to start bot first
            Log::info('User has not started bot chat for /login', [
                'user_id' => $message->userId,
                'error' => $e->getMessage(),
            ]);

            $username = $message->username ? '@' . $message->username : $message->firstName;
            $botUsername = config('telegram.bot_username', 'WagerBot');

            $initMessage = "👋 Hi {$username}! To receive your login link privately, please start a chat with me first.\n\n";
            $initMessage .= "Search for @{$botUsername} in Telegram and send me /start, then try /login again!";

            $this->messenger->sendMessage(
                OutgoingMessage::text($message->chatId, $initMessage)
            );
        }
    }

    public function getCommand(): string
    {
        return '/login';
    }

    public function getDescription(): string
    {
        return 'Get a login link to access the webapp';
    }
}
