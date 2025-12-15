<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Test command for badge image notifications
 *
 * Usage: php artisan telegram:test-badge {chat_id}
 */
class TestBadgeNotification extends Command
{
    protected $signature = 'telegram:test-badge {chat_id}';
    protected $description = 'Test sending a badge image to a Telegram chat';

    public function handle(): int
    {
        $chatId = $this->argument('chat_id');
        $badgePath = storage_path('badges/dispute-judge.png');

        if (!file_exists($badgePath)) {
            $this->error("Badge file not found: {$badgePath}");
            return 1;
        }

        $bot = new BotApi(config('telegram.bot_token'));

        $this->info("Testing badge notification to chat: {$chatId}");

        // Test 1: Send photo with caption and inline keyboard
        $this->info("\n📷 Test 1: Sending photo with local file upload...");

        try {
            $caption = "🎖️ <b>Badge Earned!</b>\n\n" .
                       "<b>John</b> just earned the <b>Dispute Judge</b> badge!\n\n" .
                       "🏛️ <i>Participated in 1 dispute resolution</i>";

            $keyboard = new InlineKeyboardMarkup([
                [['text' => '👤 View Profile', 'callback_data' => 'profile:123']],
                [['text' => '🏆 All Badges', 'url' => config('app.url') . '/badges']],
            ]);

            // Create a CURLFile for upload
            $photo = new \CURLFile($badgePath, 'image/png', 'badge.png');

            $result = $bot->sendPhoto(
                $chatId,
                $photo,
                $caption,
                null,
                $keyboard,
                false,
                'HTML'
            );

            $this->info("✅ Photo sent successfully!");
            $this->info("   Message ID: " . $result->getMessageId());

        } catch (\Exception $e) {
            $this->error("❌ Failed: " . $e->getMessage());
        }

        // Test 2: Send message with embedded image URL (alternative approach)
        $this->info("\n📝 Test 2: Sending text message with mention...");

        try {
            $textMessage = "🎖️ <b>New Badge Alert!</b>\n\n" .
                          "Congratulations <b>John</b>! You earned the <b>Dispute Judge</b> badge 🏛️\n\n" .
                          "Keep up the great work in helping resolve disputes fairly!";

            $bot->sendMessage(
                $chatId,
                $textMessage,
                'HTML'
            );

            $this->info("✅ Text message sent!");

        } catch (\Exception $e) {
            $this->error("❌ Failed: " . $e->getMessage());
        }

        $this->newLine();
        $this->info("Test complete! Check your Telegram chat.");

        return 0;
    }
}
