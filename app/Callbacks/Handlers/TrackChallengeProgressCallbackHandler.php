<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Models\Challenge;
use Illuminate\Support\Facades\Log;

/**
 * Handle track_challenge_progress callback - User wants to track challenge progress
 */
class TrackChallengeProgressCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "track_challenge_progress:{challenge_id}"
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
        $challenge = Challenge::with(['group', 'creator', 'acceptor'])->find($challengeId);
        if (!$challenge) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Challenge not found',
                showAlert: true
            );
            return;
        }

        try {
            // Build challenge progress message
            $message = $this->buildProgressMessage($challenge);

            // Send the message
            $this->messenger->sendMessage(
                OutgoingMessage::markdown($callback->chatId, $message)
            );

            // Answer the callback
            $this->messenger->answerCallback(
                $callback->callbackId,
                '✅ Progress sent',
                showAlert: false
            );

        } catch (\Exception $e) {
            Log::error('Error tracking challenge progress', [
                'error' => $e->getMessage(),
                'challenge_id' => $challengeId,
                'user_id' => $callback->userId,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error loading challenge progress',
                showAlert: true
            );
        }
    }

    /**
     * Build challenge progress message
     */
    private function buildProgressMessage(Challenge $challenge): string
    {
        $group = $challenge->group;
        $currencyName = $group->points_currency_name ?? 'points';

        $message = "🎯 *Challenge Progress*\n\n";
        $message .= "💪 *{$challenge->description}*\n\n";

        $message .= "*Status:* " . ucfirst($challenge->status) . "\n";
        $message .= "*Reward:* " . $challenge->getAbsoluteAmount() . " {$currencyName}\n";
        $message .= "*Creator:* " . ($challenge->creator->name ?? 'Unknown') . "\n";

        if ($challenge->acceptor) {
            $message .= "*Accepted by:* " . $challenge->acceptor->name . "\n";
        }

        // Deadlines
        if ($challenge->acceptance_deadline && $challenge->status === 'open') {
            $deadline = $challenge->acceptance_deadline;
            if ($deadline > now()) {
                $message .= "*Acceptance closes:* " . $deadline->diffForHumans() . "\n";
            } else {
                $message .= "*Acceptance closed:* " . $deadline->diffForHumans() . "\n";
            }
        }

        if ($challenge->completion_deadline) {
            $deadline = $challenge->completion_deadline;
            if ($deadline > now()) {
                $message .= "*Complete by:* " . $deadline->diffForHumans();
            } else {
                $message .= "*Deadline was:* " . $deadline->diffForHumans();
            }
        }

        // Show outcome if completed
        if ($challenge->status === 'completed') {
            $message .= "\n\n✅ *Challenge completed!*";
        } elseif ($challenge->status === 'expired') {
            $message .= "\n\n⏰ *Challenge expired*";
        }

        return $message;
    }

    public function getAction(): string
    {
        return 'track_challenge_progress';
    }

    public function getDescription(): string
    {
        return 'Track challenge progress';
    }
}
