<?php

declare(strict_types=1);

namespace App\Commands;

use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\MessengerAdapterInterface;
use Illuminate\Support\Facades\Log;

/**
 * Base class for command handlers providing common functionality
 */
abstract class AbstractCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        protected readonly MessengerAdapterInterface $messenger
    ) {}

    /**
     * Default: no aliases
     *
     * @return array<string>
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Send a reaction to the message if the platform supports it
     *
     * This reduces noise in group chats by using emoji reactions instead of text confirmations
     */
    protected function sendReaction(IncomingMessage $message, string $emoji): void
    {
        // Only works for Telegram platform
        if ($message->platform !== 'telegram') {
            return;
        }

        // Get the Telegram adapter and set reaction
        if ($this->messenger instanceof \App\Messaging\Adapters\TelegramAdapter) {
            try {
                $messageId = (int) ($message->metadata['message_id'] ?? 0);
                if ($messageId > 0) {
                    $this->messenger->setMessageReaction($message->chatId, $messageId, $emoji, false);
                }
            } catch (\Exception $e) {
                // Silently fail if reactions aren't supported or error occurs
                Log::debug('Failed to set message reaction', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
