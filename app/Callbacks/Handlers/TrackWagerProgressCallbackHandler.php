<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Models\Wager;
use Illuminate\Support\Facades\Log;

/**
 * Handle track_progress callback - User wants to track wager progress without seeing distribution
 */
class TrackWagerProgressCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "track_progress:{wager_id}"
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
        $wager = Wager::with(['group', 'entries'])->find($wagerId);
        if (!$wager) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Wager not found',
                showAlert: true
            );
            return;
        }

        try {
            // Build wager progress message (without distribution details)
            $message = $this->buildProgressMessage($wager);

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
            Log::error('Error tracking wager progress', [
                'error' => $e->getMessage(),
                'wager_id' => $wagerId,
                'user_id' => $callback->userId,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error loading wager progress',
                showAlert: true
            );
        }
    }

    /**
     * Build wager progress message (summary only, no distribution)
     */
    private function buildProgressMessage(Wager $wager): string
    {
        $group = $wager->group;
        $currencyName = $group->points_currency_name ?? 'points';

        $message = "📊 *Wager Progress*\n\n";
        $message .= "*{$wager->title}*\n\n";

        $message .= "*Status:* " . ucfirst($wager->status) . "\n";
        $message .= "*Type:* " . ucfirst(str_replace('_', ' ', $wager->type)) . "\n";
        $message .= "*Stake:* {$wager->stake_amount} {$currencyName}\n";

        // Deadline
        $deadline = $wager->betting_closes_at;
        if ($deadline > now()) {
            $message .= "*Betting closes:* " . $deadline->diffForHumans() . "\n";
        } else {
            $message .= "*Betting closed:* " . $deadline->diffForHumans() . "\n";
        }

        // Participants and pot (without revealing distribution)
        $participantCount = $wager->entries->count();
        $message .= "*Participants:* {$participantCount}\n";
        $message .= "*Total pot:* {$wager->total_points_wagered} {$currencyName}";

        // Show outcome if settled
        if ($wager->status === 'settled' && $wager->outcome_value) {
            $message .= "\n\n*✅ Result:* {$wager->outcome_value}";
            $winnerCount = $wager->entries->where('is_winner', true)->count();
            $message .= "\n*Winners:* {$winnerCount}";
        }

        return $message;
    }

    public function getAction(): string
    {
        return 'track_progress';
    }

    public function getDescription(): string
    {
        return 'Track wager progress without revealing distribution';
    }
}
