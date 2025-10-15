<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Messaging\Adapters\TelegramAdapter;
use App\Models\ShortUrl;
use App\Models\Wager;
use App\Services\MessageService;
use Illuminate\Console\Command;

class SendSettlementReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'wagers:send-settlement-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send settlement reminders for wagers past their settlement trigger date that are still open';

    /**
     * Execute the console command.
     */
    public function handle(MessageService $messageService, TelegramAdapter $adapter): int
    {
        // Find wagers that are past their settlement trigger date and still open
        // Settlement trigger is expected_settlement_at if set, otherwise betting_closes_at
        $unsettledWagers = Wager::with('creator', 'group')
            ->where('status', 'open')
            ->where(function($query) {
                $query->where(function($q) {
                    // Has expected_settlement_at and it's past
                    $q->whereNotNull('expected_settlement_at')
                      ->where('expected_settlement_at', '<', now());
                })->orWhere(function($q) {
                    // No expected_settlement_at, use betting_closes_at
                    $q->whereNull('expected_settlement_at')
                      ->where('betting_closes_at', '<', now());
                });
            })
            ->whereHas('entries') // Only remind for wagers with entries
            ->get();

        if ($unsettledWagers->isEmpty()) {
            $this->info('No unsettled wagers found.');
            return self::SUCCESS;
        }

        $this->info("Found {$unsettledWagers->count()} unsettled wager(s).");

        /** @var \App\Models\Wager $wager */
        foreach ($unsettledWagers as $wager) {
            try {
                $group = $wager->group;

                // Generate reminder message with settlement buttons
                $message = $messageService->settlementReminderWithButtons($wager, isGroupChat: true);

                // Convert to OutgoingMessage for new adapter
                $outgoingMessage = new \App\Messaging\DTOs\OutgoingMessage(
                    chatId: $group->platform_chat_id,
                    text: $message->getFormattedContent(),
                    parseMode: 'HTML',
                    buttons: !empty($message->buttons) ? array_map(fn($btn) => [
                        'text' => $btn->text,
                        'callback_data' => $btn->action->value . ':' . ($btn->data ?? ''),
                    ], $message->buttons) : null
                );

                // Send to group chat (not DM!)
                $adapter->sendMessage($outgoingMessage);
                $this->info("Sent reminder to group '{$group->name}' for wager: {$wager->title}");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for wager {$wager->id}: {$e->getMessage()}");
                \Log::error('Failed to send settlement reminder', [
                    'wager_id' => $wager->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}