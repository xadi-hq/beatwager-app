<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\Message;

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