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
        // Find wagers that:
        // 1. Are still open
        // 2. Have expected_settlement_at set and it's past
        // 3. Have at least one entry
        $unsettledWagers = Wager::with(['creator', 'group', 'entries'])
            ->where('status', 'open')
            ->whereNotNull('expected_settlement_at')
            ->where('expected_settlement_at', '<', now())
            ->whereHas('entries')
            ->get();

        if ($unsettledWagers->isEmpty()) {
            $this->info('No unsettled wagers found.');
            return self::SUCCESS;
        }

        $this->info("Found {$unsettledWagers->count()} unsettled wager(s).");

        $dmsSent = 0;
        $groupMessagesSent = 0;

        /** @var \App\Models\Wager $wager */
        foreach ($unsettledWagers as $wager) {
            try {
                $group = $wager->group;
                $expectedSettlement = $wager->expected_settlement_at;
                $now = now();

                // Calculate time since expected settlement
                $hoursSinceExpected = $expectedSettlement->diffInHours($now);
                $daysSinceExpected = $expectedSettlement->diffInDays($now);

                // Determine if we should send a reminder
                $shouldSendDM = $hoursSinceExpected >= 1 && $wager->settlement_reminder_count === 0;
                $shouldSendGroup = $daysSinceExpected >= 1 &&
                    ($wager->last_settlement_reminder_sent_at === null ||
                     $wager->last_settlement_reminder_sent_at->diffInHours($now) >= 24);

                // 1 hour reminder: DM to creator
                if ($shouldSendDM) {
                    $this->sendDMReminder($wager, $messageService, $adapter);
                    $dmsSent++;
                    continue; // Skip group message for now
                }

                // 24+ hour reminder: Group message with escalation
                if ($shouldSendGroup) {
                    $this->sendGroupReminder($wager, $daysSinceExpected, $messageService, $adapter);
                    $groupMessagesSent++;
                }

            } catch (\Exception $e) {
                $this->error("Failed to process wager {$wager->id}: {$e->getMessage()}");
                \Log::error('Failed to send settlement reminder', [
                    'wager_id' => $wager->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("Sent {$dmsSent} DM reminder(s) and {$groupMessagesSent} group reminder(s).");
        return self::SUCCESS;
    }

    /**
     * Send DM reminder to wager creator (1 hour after expected settlement)
     */
    private function sendDMReminder(Wager $wager, MessageService $messageService, TelegramAdapter $adapter): void
    {
        $creator = $wager->creator;
        $messengerService = $creator->getMessengerService($wager->group->platform);

        if (!$messengerService) {
            $this->warn("Creator {$creator->name} has no messenger service for platform {$wager->group->platform}");
            return;
        }

        // Generate signed URL for settlement
        $settleUrl = \URL::signedRoute('wager.show', ['wager' => $wager->id], now()->addDays(7));

        // Generate DM message
        $message = $messageService->settlementReminderDM($wager, $settleUrl);

        // Send DM
        $outgoingMessage = new \App\Messaging\DTOs\OutgoingMessage(
            chatId: $messengerService->platform_user_id,
            text: $message->getFormattedContent(),
            parseMode: 'HTML',
            buttons: !empty($message->buttons) ? [array_map(fn($btn) => [
                'text' => $btn->label,
                'url' => $btn->value,
            ], $message->buttons)] : null
        );

        $adapter->sendDirectMessage($messengerService->platform_user_id, $outgoingMessage);

        // Update wager tracking
        $wager->update([
            'last_settlement_reminder_sent_at' => now(),
            'settlement_reminder_count' => 1,
        ]);

        $this->info("Sent DM reminder to {$creator->name} for wager: {$wager->title}");
    }

    /**
     * Send group reminder (24+ hours after expected settlement)
     */
    private function sendGroupReminder(Wager $wager, int $daysWaiting, MessageService $messageService, TelegramAdapter $adapter): void
    {
        $group = $wager->group;

        // Generate escalating group message
        $message = $messageService->settlementReminderGroup($wager, $daysWaiting);

        // Convert buttons to Telegram format
        $buttons = [];
        if (!empty($message->buttons)) {
            foreach ($message->buttons as $row) {
                $buttonRow = [];
                foreach ($row as $btn) {
                    $buttonRow[] = [
                        'text' => $btn->label,
                        'callback_data' => $btn->value,
                    ];
                }
                $buttons[] = $buttonRow;
            }
        }

        // Send to group chat
        $outgoingMessage = new \App\Messaging\DTOs\OutgoingMessage(
            chatId: $group->platform_chat_id,
            text: $message->getFormattedContent(),
            parseMode: 'HTML',
            buttons: !empty($buttons) ? $buttons : null
        );

        $adapter->sendMessage($outgoingMessage);

        // Update wager tracking
        $wager->update([
            'last_settlement_reminder_sent_at' => now(),
            'settlement_reminder_count' => $wager->settlement_reminder_count + 1,
        ]);

        $this->info("Sent group reminder (day {$daysWaiting}) to '{$group->name}' for wager: {$wager->title}");
    }
}