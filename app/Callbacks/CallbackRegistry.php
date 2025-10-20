<?php

declare(strict_types=1);

namespace App\Callbacks;

use App\Messaging\DTOs\IncomingCallback;
use Illuminate\Support\Facades\Log;

/**
 * Registry for callback handlers
 *
 * Maps callback actions (e.g., 'challenge_accept') to their respective handlers
 * Uses a hash map for O(1) lookup performance
 */
class CallbackRegistry
{
    /**
     * @var array<string, CallbackHandlerInterface>
     */
    private array $handlers = [];

    private ?CallbackHandlerInterface $fallbackHandler = null;

    /**
     * Register a callback handler
     */
    public function register(CallbackHandlerInterface $handler): void
    {
        $action = $handler->getAction();

        if (isset($this->handlers[$action])) {
            Log::warning("Callback handler for action '{$action}' is being overwritten");
        }

        $this->handlers[$action] = $handler;
    }

    /**
     * Set fallback handler for unknown callback actions
     */
    public function setFallbackHandler(CallbackHandlerInterface $handler): void
    {
        $this->fallbackHandler = $handler;
    }

    /**
     * Handle an incoming callback by routing to appropriate handler
     */
    public function handle(IncomingCallback $callback): void
    {
        $action = $callback->action;

        $handler = $this->handlers[$action] ?? $this->fallbackHandler;

        if ($handler === null) {
            Log::warning("No handler found for callback action: {$action}");
            return;
        }

        Log::info("Routing callback to handler", [
            'action' => $action,
            'handler' => get_class($handler),
        ]);

        $handler->handle($callback);
    }

    /**
     * Get all registered handlers
     *
     * @return array<string, CallbackHandlerInterface>
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
