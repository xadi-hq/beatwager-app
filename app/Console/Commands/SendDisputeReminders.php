<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Dispute;
use App\Services\MessageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDisputeReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'disputes:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send vote reminders for disputes expiring within 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(MessageService $messageService): int
    {
        // Find pending disputes expiring within the next 24 hours
        // that haven't received a reminder yet
        $disputes = Dispute::query()
            ->pending()
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addHours(24))
            ->whereNull('reminder_sent_at')
            ->with(['group', 'disputable', 'votes'])
            ->get();

        if ($disputes->isEmpty()) {
            $this->info('No disputes need reminders.');
            return self::SUCCESS;
        }

        $this->info("Found {$disputes->count()} dispute(s) needing reminders.");

        $remindersSent = 0;

        foreach ($disputes as $dispute) {
            try {
                $group = $dispute->group;

                if (!$group) {
                    $this->warn("Dispute {$dispute->id} has no group, skipping.");
                    continue;
                }

                // Check if we already have enough votes
                if ($dispute->hasEnoughVotes()) {
                    $this->info("Dispute {$dispute->id} already has enough votes, skipping reminder.");
                    continue;
                }

                // Generate reminder message
                $message = $messageService->disputeVoteReminder($dispute);

                // Send to group
                $group->sendMessage($message);

                // Mark reminder as sent
                $dispute->update(['reminder_sent_at' => now()]);

                $remindersSent++;

                $this->info("Sent reminder for dispute {$dispute->id} to group '{$group->name}'");

            } catch (\Exception $e) {
                $this->error("Failed to send reminder for dispute {$dispute->id}: {$e->getMessage()}");
                Log::error('Failed to send dispute reminder', [
                    'dispute_id' => $dispute->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("Sent {$remindersSent} dispute vote reminder(s).");
        return self::SUCCESS;
    }
}
