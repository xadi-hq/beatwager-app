<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\Message;

/**
 * @deprecated Use App\Messaging\MessengerAdapterInterface instead
 *
 * This interface is being phased out in favor of the newer MessengerAdapterInterface
 * which provides DM support, webhook handling, and a more comprehensive feature set.
 *
 * Existing code using this interface will continue to work via MessengerBridge,
 * but new code should inject MessengerAdapterInterface directly.
 */
interface MessengerInterface
{
    /**
     * Send a message to a chat/channel
     *
     * @param Message $message The platform-agnostic message
     * @param string $chatId Platform-specific chat identifier
     * @return void
     */
    public function send(Message $message, string $chatId): void;

    /**
     * Format message content for the specific platform
     *
     * @param Message $message The platform-agnostic message
     * @return string Formatted message (HTML, Markdown, etc.)
     */
    public function formatMessage(Message $message): string;

    /**
     * Build platform-specific buttons/keyboard
     *
     * @param array $buttons Array of Button DTOs
     * @return mixed Platform-specific keyboard object
     */
    public function buildButtons(array $buttons): mixed;
}