<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Models\ShortUrl;
use App\Models\Wager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Handle join_complex_wager callback - User wants to join a wager requiring complex input
 * Sends DM with signed URL to join form (consistent with newwager, view details pattern)
 */
class JoinComplexWagerCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "join_complex_wager:{wager_id}"
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid wager format',
                showAlert: true
            );
            return;
        }

        $wagerId = $callback->data;

        // Find the wager
        $wager = Wager::with(['group'])->find($wagerId);
        if (!$wager) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Wager not found',
                showAlert: true
            );
            return;
        }

        // Check if wager is still open
        if ($wager->status !== 'open') {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ This wager is no longer accepting bets',
                showAlert: true
            );
            return;
        }

        // Check if betting deadline has passed
        if ($wager->betting_closes_at < now()) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Betting deadline has passed',
                showAlert: true
            );
            return;
        }

        $botUsername = config('telegram.bot_username', 'WagerBot');

        // Try to send DM - if it fails, user hasn't started chat with bot
        try {
            // Generate signed URL with encrypted user ID
            $signedUrl = URL::temporarySignedRoute(
                'wager.join',
                now()->addMinutes(30),
                [
                    'wager' => $wagerId,
                    'u' => encrypt($callback->platform . ':' . $callback->userId),
                    'username' => $callback->username,
                    'first_name' => $callback->firstName,
                    'last_name' => $callback->lastName,
                ]
            );

            // Create short URL
            $shortCode = ShortUrl::generateUniqueCode(6);
            ShortUrl::create([
                'code' => $shortCode,
                'target_url' => $signedUrl,
                'expires_at' => now()->addMinutes(30),
            ]);

            // Build short URL
            $shortUrlFull = url('/l/' . $shortCode);

            // Send short URL as direct message to user
            $dmMessage = "📝 *Join Wager*\n\n";
            $dmMessage .= "💰 Wager: {$wager->title}\n";
            $dmMessage .= "🎯 Stake: {$wager->stake_amount} points\n\n";
            $dmMessage .= "Click the link to submit your answer:\n";
            $dmMessage .= $shortUrlFull . "\n\n";
            $dmMessage .= "⏰ Link expires in 30 minutes";

            $this->messenger->sendDirectMessage(
                $callback->userId,
                OutgoingMessage::markdown($callback->chatId, $dmMessage)
            );

            // Answer callback query with success
            $this->messenger->answerCallback(
                $callback->callbackId,
                '✅ Join link sent to your private chat!'
            );

        } catch (\Exception $dmError) {
            // If DM fails, user hasn't started chat with bot
            Log::info('User has not started bot chat for join wager', [
                'user_id' => $callback->userId,
                'wager_id' => $wagerId,
                'error' => $dmError->getMessage(),
            ]);

            // Answer callback with instruction to start bot chat
            $this->messenger->answerCallback(
                $callback->callbackId,
                "Please start a chat with @{$botUsername} first and send /start, then try again!",
                showAlert: true
            );

            return;
        }
    }

    public function getAction(): string
    {
        return 'join_complex_wager';
    }

    public function getDescription(): string
    {
        return 'Join a wager requiring complex input (sends DM with form link)';
    }
}
