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
 * Handle /newevent command - Create a new event
 */
class NewEventCommandHandler extends AbstractCommandHandler
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
                    "❌ Please use /newevent in a group chat where you want to create the event.\n\n" .
                    "I need to know which group the event is for!"
                )
            );
            return;
        }

        // Get chat info from metadata
        $chat = $message->getChat();

        // Generate signed URL with all context needed for event creation
        $fullUrl = $this->messenger->createAuthenticatedUrl(
            'events.create',
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

        // Send link to user's private chat
        try {
            $username = $message->username ? '@' . $message->username : $message->firstName;
            $chatTitle = $chat->getTitle();

            $dmMessage = "📅 *Create a new event for {$chatTitle}*\n\n";
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
            // If we can't send DM (user hasn't started bot), send link in group as fallback
            Log::warning('Could not send DM to user, falling back to group message', [
                'user_id' => $message->userId,
                'error' => $e->getMessage(),
            ]);

            $fallbackMessage = "📅 *Create your event for this group!*\n\n";
            $fallbackMessage .= "Click the link to open the creation form:\n";
            $fallbackMessage .= $shortUrlFull . "\n\n";
            $fallbackMessage .= "⏱️ Link expires in 30 minutes\n\n";
            $fallbackMessage .= "💡 Tip: Start a private chat with me first using /start so I can send you links privately!";

            $this->messenger->sendMessage(
                OutgoingMessage::markdown($message->chatId, $fallbackMessage)
            );
        }
    }

    public function getCommand(): string
    {
        return '/newevent';
    }

    public function getDescription(): string
    {
        return 'Create a new group event with attendance tracking';
    }
}
