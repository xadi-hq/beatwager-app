<?php

declare(strict_types=1);

namespace App\Commands;

use App\Messaging\DTOs\IncomingMessage;

/**
 * Registry for command handlers with routing logic
 */
class CommandRegistry
{
    /**
     * @var array<string, CommandHandlerInterface>
     */
    private array $handlers = [];

    private ?CommandHandlerInterface $fallbackHandler = null;

    /**
     * Register a command handler
     */
    public function register(CommandHandlerInterface $handler): void
    {
        // Register primary command
        $this->handlers[$handler->getCommand()] = $handler;

        // Register aliases
        foreach ($handler->getAliases() as $alias) {
            $this->handlers[$alias] = $handler;
        }
    }

    /**
     * Set the fallback handler for unknown commands
     */
    public function setFallbackHandler(CommandHandlerInterface $handler): void
    {
        $this->fallbackHandler = $handler;
    }

    /**
     * Handle an incoming message by routing to appropriate handler
     */
    public function handle(IncomingMessage $message): void
    {
        $command = $this->extractCommand($message->getText());

        $handler = $this->handlers[$command] ?? $this->fallbackHandler;

        if ($handler === null) {
            throw new \RuntimeException("No handler registered for command: {$command}");
        }

        $handler->handle($message);
    }

    /**
     * Extract command from message text
     *
     * Handles both /command and /command@BotUsername formats
     */
    private function extractCommand(string $text): string
    {
        // Extract first word (command) from message
        $command = strtok($text, ' ') ?: '';

        // Strip bot username suffix if present (e.g., /newwager@WagerBot -> /newwager)
        $botUsername = config('telegram.bot_username');
        if ($botUsername && str_contains($command, '@')) {
            $command = preg_replace('/@' . preg_quote($botUsername, '/') . '$/', '', $command);
        }

        return $command;
    }

    /**
     * Get all registered commands for help text
     *
     * @return array<CommandHandlerInterface>
     */
    public function getAllHandlers(): array
    {
        // Return unique handlers (excluding duplicates from aliases)
        return array_values(array_unique($this->handlers, SORT_REGULAR));
    }
}
