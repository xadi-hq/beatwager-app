<?php

declare(strict_types=1);

namespace App\Commands;

use App\Messaging\DTOs\IncomingMessage;

/**
 * Interface for bot command handlers
 *
 * Each command (e.g., /start, /help, /newwager) implements this interface
 * to handle incoming messages in a consistent, testable way.
 */
interface CommandHandlerInterface
{
    /**
     * Handle the incoming command message
     */
    public function handle(IncomingMessage $message): void;

    /**
     * Get the primary command string (e.g., '/start')
     */
    public function getCommand(): string;

    /**
     * Get command aliases (e.g., ['/mybets'] for /mywagers)
     *
     * @return array<string>
     */
    public function getAliases(): array;

    /**
     * Get command description for help text
     */
    public function getDescription(): string;
}
