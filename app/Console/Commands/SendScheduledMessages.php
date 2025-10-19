<?php

namespace App\Console\Commands;

use App\Services\MessageService;
use App\Services\ScheduledMessageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:send-scheduled
                            {--dry-run : Preview messages without sending}
                            {--force : Send all active messages regardless of scheduled date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all scheduled messages that are due today';

    /**
     * Execute the console command.
     */
    public function handle(
        ScheduledMessageService $scheduledMessageService,
        MessageService $messageService
    ): int
    {
        $isDryRun = $this->option('dry-run');
        $isForced = $this->option('force');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No messages will be sent');
        }

        if ($isForced) {
            $this->warn('⚡ FORCE MODE - Sending all active messages regardless of schedule');
        }

        $messages = $isForced
            ? $scheduledMessageService->getForGroup(null, ['is_active' => true])
            : $scheduledMessageService->getMessagesToSendToday();

        if ($messages->isEmpty()) {
            $this->info('✅ No scheduled messages to send today');
            return Command::SUCCESS;
        }

        $this->info("📅 Found {$messages->count()} scheduled message(s) to send");

        foreach ($messages as $scheduledMessage) {
            $group = $scheduledMessage->group;

            $this->line("  → [{$group->name}] {$scheduledMessage->title} ({$scheduledMessage->message_type})");

            if ($isDryRun) {
                continue;
            }

            try {
                // Generate the message
                $message = $messageService->scheduledMessage($group, $scheduledMessage);

                // Send to group
                $group->sendMessage($message);

                // Mark as sent
                $scheduledMessageService->markAsSent($scheduledMessage);

                $this->info("    ✅ Sent successfully");

                Log::channel('operational')->info('scheduled_message.sent_success', [
                    'group_id' => $group->id,
                    'message_id' => $scheduledMessage->id,
                    'title' => $scheduledMessage->title,
                ]);

            } catch (\Exception $e) {
                $this->error("    ❌ Failed: {$e->getMessage()}");

                Log::channel('operational')->error('scheduled_message.send_failed', [
                    'group_id' => $group->id,
                    'message_id' => $scheduledMessage->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $summary = $isDryRun
            ? "Dry run complete - {$messages->count()} message(s) would be sent"
            : "✅ Sent {$messages->count()} scheduled message(s)";

        $this->info($summary);

        return Command::SUCCESS;
    }
}
