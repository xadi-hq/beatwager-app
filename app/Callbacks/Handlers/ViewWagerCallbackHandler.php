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
 * Handle view callback - User wants to view wager details/progress
 */
class ViewWagerCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "view:{wager_id}"
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
        $wager = Wager::with(['group', 'entries.user'])->find($wagerId);
        if (!$wager) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Wager not found',
                showAlert: true
            );
            return;
        }

        try {
            // Generate signed URL with encrypted user ID
            $signedUrl = URL::temporarySignedRoute(
                'wagers.show',
                now()->addDays(30),
                [
                    'wager' => $wagerId,
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
                '📊 Opening wager details...'
            );

            // Send short URL as direct message to user
            $dmMessage = "📊 *View Wager Details*\n\n";
            $dmMessage .= "💰 Wager: {$wager->title}\n\n";
            $dmMessage .= "Click to view full details and distribution:\n";
            $dmMessage .= $shortUrlFull;

            try {
                $this->messenger->sendDirectMessage(
                    $callback->userId,
                    OutgoingMessage::markdown($callback->chatId, $dmMessage)
                );
            } catch (\Exception $e) {
                // If DM fails, log warning
                Log::warning('Failed to send wager view DM', [
                    'user_id' => $callback->userId,
                    'error' => $e->getMessage(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error viewing wager details', [
                'error' => $e->getMessage(),
                'wager_id' => $wagerId,
                'user_id' => $callback->userId,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error loading wager details',
                showAlert: true
            );
        }
    }

    public function getAction(): string
    {
        return 'view';
    }

    public function getDescription(): string
    {
        return 'View wager details and progress';
    }
}
