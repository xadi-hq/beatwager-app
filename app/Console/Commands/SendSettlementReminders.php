<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ShortUrl;
use App\Models\Wager;
use App\Services\MessageService;
use App\Services\Messengers\TelegramMessenger;
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
    protected $description = 'Send settlement reminders for wagers past deadline that are still open';

    /**
     * Execute the console command.
     */
    public function handle(MessageService $messageService, TelegramMessenger $messenger): int
    {
        // Find wagers that are past deadline and still open
        $unsettledWagers = Wager::with('creator', 'group')
            ->where('status', 'open')
            ->where('deadline', '<', now())
            ->get();

        if ($unsettledWagers->isEmpty()) {
            $this->info('No unsettled wagers found.');
            return self::SUCCESS;
        }

        $this->info("Found {$unsettledWagers->count()} unsettled wager(s).");

        /** @var \App\Models\Wager $wager */
        foreach ($unsettledWagers as $wager) {
            try {
                // Get creator's Telegram service
                /** @var \App\Models\User $creator */
                $creator = $wager->creator;
                $telegramService = $creator->getTelegramService();

                if (!$telegramService) {
                    $this->warn("Creator {$creator->name} has no Telegram service linked. Skipping.");
                    continue;
                }

                // Generate signed URL for the wager (platform-agnostic format)
                $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'wager.show',
                    now()->addDays(30),
                    [
                        'wager' => $wager->id,
                        'u' => encrypt('telegram:' . $telegramService->platform_user_id)
                    ]
                );

                // Create short URL
                $shortCode = ShortUrl::generateUniqueCode(6);
                ShortUrl::create([
                    'code' => $shortCode,
                    'target_url' => $signedUrl,
                    'expires_at' => now()->addDays(30),
                ]);

                $shortUrl = url('/l/' . $shortCode);

                // Generate reminder message
                $message = $messageService->settlementReminder($wager, $shortUrl);

                // Send to creator's DM (use platform_user_id)
                $messenger->send($message, $telegramService->platform_user_id);
                $this->info("Sent reminder to {$creator->name} for wager: {$wager->title}");
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