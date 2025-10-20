<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Models\ShortUrl;
use App\Models\Wager;
use Illuminate\Support\Facades\Log;

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
            // Build wager details message
            $message = $this->buildWagerDetailsMessage($wager);

            // Create short URL for wager details page
            $shortCode = ShortUrl::generateUniqueCode(6);
            ShortUrl::create([
                'code' => $shortCode,
                'target_url' => config('app.url') . '/wagers/' . $wager->id,
                'expires_at' => now()->addDays(30),
            ]);
            $shortUrl = url('/l/' . $shortCode);

            $message .= "\n\n📊 Full details: {$shortUrl}";

            // Send the message
            $this->messenger->sendMessage(
                OutgoingMessage::markdown($callback->chatId, $message)
            );

            // Answer the callback
            $this->messenger->answerCallback(
                $callback->callbackId,
                '✅ Wager details sent',
                showAlert: false
            );

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

    /**
     * Build wager details message
     */
    private function buildWagerDetailsMessage(Wager $wager): string
    {
        $group = $wager->group;
        $currencyName = $group->points_currency_name ?? 'points';

        $message = "📊 *Wager Details*\n\n";
        $message .= "*{$wager->title}*\n";

        if ($wager->description) {
            $message .= "_{$wager->description}_\n";
        }

        $message .= "\n*Status:* " . ucfirst($wager->status);
        $message .= "\n*Type:* " . ucfirst(str_replace('_', ' ', $wager->type));
        $message .= "\n*Stake:* {$wager->stake_amount} {$currencyName}";

        // Deadline
        $deadline = $wager->betting_closes_at;
        if ($deadline > now()) {
            $message .= "\n*Betting closes:* " . $deadline->diffForHumans();
        } else {
            $message .= "\n*Betting closed:* " . $deadline->diffForHumans();
        }

        // Participants
        $participantCount = $wager->entries->count();
        $message .= "\n*Participants:* {$participantCount}";
        $message .= "\n*Total pot:* {$wager->total_points_wagered} {$currencyName}";

        // Show options for multiple choice
        if ($wager->type === 'multiple_choice' && $wager->options) {
            $message .= "\n\n*Options:*\n";
            foreach ($wager->options as $option) {
                $count = $wager->entries->where('answer_value', $option)->count();
                $message .= "• {$option} ({$count} bets)\n";
            }
        }

        // Show distribution for binary
        if ($wager->type === 'binary') {
            $yesCount = $wager->entries->where('answer_value', 'yes')->count();
            $noCount = $wager->entries->where('answer_value', 'no')->count();
            $message .= "\n\n*Distribution:*\n";
            $message .= "• Yes: {$yesCount} bets\n";
            $message .= "• No: {$noCount} bets";
        }

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
        return 'view';
    }

    public function getDescription(): string
    {
        return 'View wager details and progress';
    }
}
