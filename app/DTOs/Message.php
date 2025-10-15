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
     */
    public function __construct(
        public string $content,
        public MessageType $type,
        public array $variables = [],
        public array $buttons = [],
        public ?object $context = null,
    ) {}

    /**
     * Replace placeholders in content with variables
     */
    public function getFormattedContent(): string
    {
        $content = $this->content;
        foreach ($this->variables as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        return $content;
    }
}