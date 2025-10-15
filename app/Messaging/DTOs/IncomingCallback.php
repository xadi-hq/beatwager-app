<?php

declare(strict_types=1);

namespace App\Messaging\DTOs;

/**
 * Platform-agnostic representation of an incoming callback query
 *
 * Represents a callback query from inline buttons (Telegram) or interactive components (Slack/Discord)
 */
readonly class IncomingCallback
{
    public function __construct(
        public string $callbackId,
        public string $action,
        public ?string $data,
        public string $userId,
        public string $chatId,
        public string $platform,
        public ?string $username = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public array $metadata = []
    ) {}

    /**
     * Get the full callback data (action:data format)
     */
    public function getFullData(): string
    {
        return $this->data ? "{$this->action}:{$this->data}" : $this->action;
    }

    /**
     * Create from Telegram callback query
     */
    public static function fromTelegram(array $callbackQuery): self
    {
        $from = $callbackQuery['from'] ?? [];
        $data = $callbackQuery['data'] ?? '';

        // Parse callback data format: "action:data" or just "action"
        $parts = explode(':', $data, 2);
        $action = $parts[0] ?? '';
        $callbackData = $parts[1] ?? null;

        return new self(
            callbackId: (string) ($callbackQuery['id'] ?? ''),
            action: $action,
            data: $callbackData,
            userId: (string) ($from['id'] ?? ''),
            chatId: (string) ($callbackQuery['message']['chat']['id'] ?? ''),
            platform: 'telegram',
            username: $from['username'] ?? null,
            firstName: $from['first_name'] ?? null,
            lastName: $from['last_name'] ?? null,
            metadata: $callbackQuery
        );
    }
}
