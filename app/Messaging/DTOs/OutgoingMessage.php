<?php

declare(strict_types=1);

namespace App\Messaging\DTOs;

/**
 * Platform-agnostic message to be sent
 *
 * Represents a message to send via any messaging platform
 */
readonly class OutgoingMessage
{
    public function __construct(
        public string $chatId,                  // Destination chat/channel ID
        public string $text,                    // Message text
        public ?string $parseMode = null,       // 'Markdown', 'HTML', null
        public ?array $buttons = null,          // Inline buttons/actions
        public ?array $keyboard = null,         // Reply keyboard
        public bool $disablePreview = false,    // Disable link previews
        public array $metadata = [],            // Platform-specific options
    ) {}

    /**
     * Create a simple text message
     */
    public static function text(string $chatId, string $text): self
    {
        return new self(chatId: $chatId, text: $text);
    }

    /**
     * Create a message with inline buttons
     */
    public static function withButtons(string $chatId, string $text, array $buttons): self
    {
        return new self(
            chatId: $chatId,
            text: $text,
            buttons: $buttons,
            parseMode: 'Markdown'
        );
    }

    /**
     * Create a markdown-formatted message
     */
    public static function markdown(string $chatId, string $text): self
    {
        return new self(
            chatId: $chatId,
            text: $text,
            parseMode: 'Markdown'
        );
    }

    /**
     * Create an HTML-formatted message
     */
    public static function html(string $chatId, string $text): self
    {
        return new self(
            chatId: $chatId,
            text: $text,
            parseMode: 'HTML'
        );
    }

    /**
     * Fluent method to enable markdown
     */
    public function withMarkdown(): self
    {
        return new self(
            chatId: $this->chatId,
            text: $this->text,
            parseMode: 'Markdown',
            buttons: $this->buttons,
            keyboard: $this->keyboard,
            disablePreview: $this->disablePreview,
            metadata: $this->metadata,
        );
    }

    /**
     * Fluent method to disable link preview
     */
    public function withoutPreview(): self
    {
        return new self(
            chatId: $this->chatId,
            text: $this->text,
            parseMode: $this->parseMode,
            buttons: $this->buttons,
            keyboard: $this->keyboard,
            disablePreview: true,
            metadata: $this->metadata,
        );
    }
}
