<?php

declare(strict_types=1);

namespace App\Messaging\DTOs;

/**
 * Platform-agnostic representation of incoming messages
 *
 * Normalizes messages from Telegram, Slack, Discord into a unified structure
 */
readonly class IncomingMessage
{
    public function __construct(
        public string $platform,           // 'telegram', 'slack', 'discord'
        public string $messageId,          // Platform-specific message ID
        public MessageType $type,          // 'command', 'text', 'callback', 'interaction'
        public string $chatId,             // Channel/Group ID
        public string $userId,             // User ID
        public ?string $username,          // Platform username
        public ?string $firstName,         // User first name
        public ?string $lastName,          // User last name
        public ?string $text,              // Message text content
        public ?string $command,           // Extracted command (e.g., '/start')
        public ?array $commandArgs,        // Command arguments
        public ?string $callbackData,      // Callback/interaction data
        public array $metadata,            // Platform-specific extras
    ) {}

    /**
     * Check if this is a command message
     */
    public function isCommand(): bool
    {
        return $this->type === MessageType::COMMAND && $this->command !== null;
    }

    /**
     * Check if this is a callback/interaction
     */
    public function isCallback(): bool
    {
        return $this->type === MessageType::CALLBACK && $this->callbackData !== null;
    }

    /**
     * Get chat type (private, group, supergroup, channel)
     */
    public function getChatType(): string
    {
        return $this->metadata['chat_type'] ?? 'private';
    }

    /**
     * Check if message is in a group context
     */
    public function isGroupContext(): bool
    {
        return in_array($this->getChatType(), ['group', 'supergroup', 'channel']);
    }

    /**
     * Get the full text (convenience method)
     */
    public function getText(): string
    {
        return $this->text ?? '';
    }

    /**
     * Get platform-specific chat object from metadata
     */
    public function getChat(): mixed
    {
        return $this->metadata['chat'] ?? null;
    }

    /**
     * Get platform-specific user/from object from metadata
     */
    public function getFrom(): mixed
    {
        return $this->metadata['from'] ?? null;
    }

    /**
     * Get the chat ID as string
     */
    public function getChatId(): string
    {
        return $this->chatId;
    }
}
