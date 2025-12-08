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
 * Handle elimination_view callback - User wants to view elimination challenge details
 */
class EliminationViewCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "elimination_view:{challenge_id}"
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

        // Generate signed URL with encrypted user ID
        $signedUrl = URL::temporarySignedRoute(
            'elimination.show',
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
            '📊 Opening challenge details...'
        );

        // Get challenge stats
        $survivorCount = $challenge->getSurvivorCount();
        $eliminatedCount = $challenge->getEliminatedCount();
        $group = $challenge->group;
        $currency = $group->points_currency_name ?? 'points';

        // Send short URL as direct message to user
        $dmMessage = "📊 *Elimination Challenge Details*\n\n";
        $dmMessage .= "💪 Challenge: {$challenge->description}\n";
        $dmMessage .= "⚠️ Trigger: {$challenge->elimination_trigger}\n";
        $dmMessage .= "🏆 Pot: {$challenge->point_pot} {$currency}\n";
        $dmMessage .= "💪 Survivors: {$survivorCount}\n";
        $dmMessage .= "💀 Eliminated: {$eliminatedCount}\n\n";
        $dmMessage .= "Click to view full details:\n";
        $dmMessage .= $shortUrlFull;

        try {
            $this->messenger->sendDirectMessage(
                $callback->userId,
                OutgoingMessage::markdown($callback->chatId, $dmMessage)
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send elimination view DM', [
                'user_id' => $callback->userId,
                'challenge_id' => $challengeId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getAction(): string
    {
        return 'elimination_view';
    }

    public function getDescription(): string
    {
        return 'View detailed information about an elimination challenge';
    }
}
