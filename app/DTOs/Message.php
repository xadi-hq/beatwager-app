<?php

declare(strict_types=1);

namespace App\DTOs;

class Message
{
    /**
     * @param string $content Plain text content with {placeholders}
     * @param MessageType $type Type of message
     * @param array $variables Key-value pairs for placeholder replacement
     * @param Button[] $buttons Array of generic button definitions
     * @param object|null $context Additional context (Wager, User, etc.)
     * @param string $currencyName Custom currency name for the group (default: 'points')
     */
    public function __construct(
        public string $content,
        public MessageType $type,
        public array $variables = [],
        public array $buttons = [],
        public ?object $context = null,
        public string $currencyName = 'points',
    ) {
        // Automatically add currency to variables if not already present
        if (!isset($this->variables['currency'])) {
            $this->variables['currency'] = $this->currencyName;
        }
    }

    /**
     * Replace placeholders in content with variables
     */
    public function getFormattedContent(): string
    {
        $content = $this->content;
        foreach ($this->variables as $key => $value) {
            $content = str_replace("{{$key}}", (string) $value, $content);
        }
        return $content;
    }
}