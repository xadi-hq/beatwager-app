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
 * Handle elimination_tap_in callback - User wants to tap in to an elimination challenge
 */
class EliminationTapInCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "elimination_tap_in:{challenge_id}"
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
        if (!$challenge || !$challenge->isEliminationChallenge()) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Challenge not found',
                showAlert: true
            );
            return;
        }

        // Check if tap-in is still open
        if ($challenge->isTapInClosed()) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '⏰ Tap-in period has ended',
                showAlert: true
            );
            return;
        }

        // Generate signed URL with encrypted user ID
        $signedUrl = URL::temporarySignedRoute(
            'elimination.tap-in',
            now()->addDays(7),
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
            'expires_at' => now()->addDays(7),
        ]);

        // Build short URL
        $shortUrlFull = url('/l/' . $shortCode);

        // Answer callback query
        $this->messenger->answerCallback(
            $callback->callbackId,
            '🎯 Opening tap-in page...'
        );

        // Send short URL as direct message to user
        $group = $challenge->group;
        $currency = $group->points_currency_name ?? 'points';

        $dmMessage = "🎯 *Tap In to Elimination Challenge*\n\n";
        $dmMessage .= "💪 Challenge: {$challenge->description}\n";
        $dmMessage .= "⚠️ Trigger: {$challenge->elimination_trigger}\n";
        $dmMessage .= "💰 Buy-in: {$challenge->buy_in_amount} {$currency}\n";
        $dmMessage .= "🏆 Pot: {$challenge->point_pot} {$currency}\n\n";
        $dmMessage .= "Click to join:\n";
        $dmMessage .= $shortUrlFull;

        try {
            $this->messenger->sendDirectMessage(
                $callback->userId,
                OutgoingMessage::markdown($callback->chatId, $dmMessage)
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send elimination tap-in DM', [
                'user_id' => $callback->userId,
                'challenge_id' => $challengeId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getAction(): string
    {
        return 'elimination_tap_in';
    }

    public function getDescription(): string
    {
        return 'Tap in to an elimination challenge';
    }
}
