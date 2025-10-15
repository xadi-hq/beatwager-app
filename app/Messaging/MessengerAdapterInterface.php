<?php

declare(strict_types=1);

namespace App\Messaging;

use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;

/**
 * Platform-agnostic messaging interface
 *
 * Normalizes different messaging platforms (Telegram, Slack, Discord)
 * into a unified API for sending/receiving messages
 */
interface MessengerAdapterInterface
{
    /**
     * Get the platform identifier (telegram, slack, discord)
     */
    public function getPlatform(): string;

    /**
     * Send a message to a user or channel
     */
    public function sendMessage(OutgoingMessage $message): void;

    /**
     * Send a direct message to a user
     */
    public function sendDirectMessage(string $userId, OutgoingMessage $message): void;

    /**
     * Answer a callback/interaction query
     */
    public function answerCallback(string $callbackId, string $text, bool $showAlert = false): void;

    /**
     * Create a short-lived authenticated URL for web actions
     */
    public function createAuthenticatedUrl(string $route, array $params, int $expiryMinutes = 30): string;

    /**
     * Verify webhook authenticity (secret token, signature, etc.)
     */
    public function verifyWebhook(array $payload, array $headers): bool;

    /**
     * Parse platform-specific webhook into normalized IncomingMessage
     */
    public function parseWebhook(array $payload): IncomingMessage;

    /**
     * Parse platform-specific callback query into normalized IncomingCallback
     */
    public function parseCallback(array $payload): IncomingCallback;
}
