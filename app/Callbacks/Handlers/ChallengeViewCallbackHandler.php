<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Models\Challenge;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Handle challenge_view callback - User wants to view challenge details
 */
class ChallengeViewCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "challenge_view:{challenge_id}"
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid challenge format',
                showAlert: true
            );
            return;
        }

        $challengeId = $callback->data;

        // Find the challenge
        $challenge = Challenge::find($challengeId);
        if (!$challenge) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Challenge not found',
                showAlert: true
            );
            return;
        }

        // Generate signed URL with encrypted user ID
        $signedUrl = URL::temporarySignedRoute(
            'challenges.show',
            now()->addDays(30),
            [
                'challenge' => $challengeId,
                'u' => encrypt($callback->platform . ':' . $callback->userId)
            ]
        );

        // Create short URL
        $shortCode = ShortUrl::generateUniqueCode(6);
        ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $signedUrl,
            'expires_at' => now()->addDays(30),
        ]);

        // Build short URL
        $shortUrlFull = url('/l/' . $shortCode);

        // Answer callback query
        $this->messenger->answerCallback(
            $callback->callbackId,
            '🎯 Opening challenge details...'
        );

        // Send short URL as direct message to user
        $dmMessage = "🎯 *View Challenge Details*\n\n";
        $dmMessage .= "💪 Challenge: {$challenge->description}\n\n";
        $dmMessage .= "Click to view details and status:\n";
        $dmMessage .= $shortUrlFull;

        try {
            $this->messenger->sendDirectMessage(
                $callback->userId,
                OutgoingMessage::markdown($callback->chatId, $dmMessage)
            );
        } catch (\Exception $e) {
            // If DM fails, log warning (could send to group chat as fallback)
            Log::warning('Failed to send challenge view DM', [
                'user_id' => $callback->userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getAction(): string
    {
        return 'challenge_view';
    }

    public function getDescription(): string
    {
        return 'View detailed information about a challenge';
    }
}
