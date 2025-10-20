<?php

declare(strict_types=1);

namespace App\Callbacks;

use App\Messaging\DTOs\IncomingCallback;

/**
 * Interface for handling callback queries from messaging platforms
 *
 * Each callback handler implements a specific callback action (e.g., challenge_accept, event_rsvp)
 */
interface CallbackHandlerInterface
{
    /**
     * Handle the incoming callback query
     */
    public function handle(IncomingCallback $callback): void;

    /**
     * Get the callback action this handler responds to (e.g., 'challenge_accept', 'event_rsvp')
     */
    public function getAction(): string;

    /**
     * Get a human-readable description of what this callback does
     */
    public function getDescription(): string;
}
