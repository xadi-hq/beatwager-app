<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Models\Dispute;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Handle dispute_view callback - User wants to view dispute details
 *
 * Callback data format: "{dispute_id}"
 */
class DisputeViewCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid dispute format',
                showAlert: true
            );
            return;
        }

        $disputeId = $callback->data;

        // Find the dispute
        $dispute = Dispute::with(['group', 'disputable', 'reporter', 'accused', 'votes'])
            ->find($disputeId);

        if (!$dispute) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Dispute not found',
                showAlert: true
            );
            return;
        }

        $botUsername = config('telegram.bot_username', 'WagerBot');

        try {
            // Generate signed URL with encrypted user ID
            $signedUrl = URL::temporarySignedRoute(
                'disputes.show',
                now()->addDays(30),
                [
                    'dispute' => $disputeId,
                    'u' => encrypt($callback->platform . ':' . $callback->userId),
                ]
            );

            // Create short URL
            $shortCode = ShortUrl::generateUniqueCode(6);
            ShortUrl::create([
                'code' => $shortCode,
                'target_url' => $signedUrl,
                'expires_at' => now()->addDays(30),
            ]);

            $shortUrlFull = url('/l/' . $shortCode);

            // Build status info
            $disputableTitle = $dispute->disputable->title ?? $dispute->disputable->name ?? 'Unknown';
            $statusEmoji = $dispute->isPending() ? '🔄' : '✅';
            $voteInfo = $dispute->isPending()
                ? "Votes: {$dispute->getVoteCount()}/{$dispute->votes_required}"
                : "Resolved: " . ($dispute->resolution?->value ?? 'unknown');

            // Send short URL as DM
            $dmMessage = "⚖️ *Dispute Details*\n\n";
            $dmMessage .= "{$statusEmoji} Item: {$disputableTitle}\n";
            $dmMessage .= "📊 {$voteInfo}\n\n";
            $dmMessage .= "Click to view full details and vote:\n";
            $dmMessage .= $shortUrlFull;

            $this->messenger->sendDirectMessage(
                $callback->userId,
                OutgoingMessage::markdown($callback->chatId, $dmMessage)
            );

            $this->messenger->answerCallback(
                $callback->callbackId,
                '✅ Link sent to your private chat!'
            );

        } catch (\Exception $e) {
            Log::info('User has not started bot chat for dispute view', [
                'user_id' => $callback->userId,
                'error' => $e->getMessage(),
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                "Please start a chat with @{$botUsername} first and send /start, then try again!",
                showAlert: true
            );
        }
    }

    public function getAction(): string
    {
        return 'dispute_view';
    }

    public function getDescription(): string
    {
        return 'View dispute details';
    }
}
