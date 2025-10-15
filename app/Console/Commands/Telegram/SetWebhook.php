<?php

declare(strict_types=1);

namespace App\Console\Commands\Telegram;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {--url= : Override webhook URL}';

    protected $description = 'Set up the Telegram bot webhook';

    public function handle(): int
    {
        $botToken = config('telegram.bot_token');

        if (empty($botToken)) {
            $this->error('Telegram bot token not configured!');
            $this->info('Set TELEGRAM_BOT_TOKEN in your .env file');
            return self::FAILURE;
        }

        $webhookUrl = $this->option('url') ?? config('telegram.webhook.url');
        $secret = config('telegram.webhook.secret');

        $this->info("Setting webhook to: {$webhookUrl}");

        $response = Http::post("https://api.telegram.org/bot{$botToken}/setWebhook", [
            'url' => $webhookUrl,
            'secret_token' => $secret,
            'allowed_updates' => ['message', 'callback_query'],
            'drop_pending_updates' => true,
        ]);

        if ($response->successful() && $response->json('ok')) {
            $this->info('✅ Webhook set successfully!');
            $this->line('');
            $this->line('Webhook URL: ' . $webhookUrl);
            $this->line('Secret token: ' . ($secret ? 'Configured' : 'Not set'));
            
            // Get webhook info
            $this->call('telegram:webhook-info');
            
            return self::SUCCESS;
        }

        $this->error('❌ Failed to set webhook');
        $this->error('Response: ' . $response->body());
        
        return self::FAILURE;
    }
}
