<?php

declare(strict_types=1);

namespace App\Console\Commands\Telegram;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WebhookInfo extends Command
{
    protected $signature = 'telegram:webhook-info';

    protected $description = 'Get current webhook information';

    public function handle(): int
    {
        $botToken = config('telegram.bot_token');

        if (empty($botToken)) {
            $this->error('Telegram bot token not configured!');
            return self::FAILURE;
        }

        $response = Http::get("https://api.telegram.org/bot{$botToken}/getWebhookInfo");

        if ($response->successful() && $response->json('ok')) {
            $info = $response->json('result');

            $this->info('📡 Webhook Information:');
            $this->line('');
            $this->line('URL: ' . ($info['url'] ?: '<not set>'));
            $this->line('Has custom certificate: ' . ($info['has_custom_certificate'] ? 'Yes' : 'No'));
            $this->line('Pending update count: ' . $info['pending_update_count']);
            
            if (isset($info['last_error_date'])) {
                $this->warn('Last error: ' . $info['last_error_message']);
                $this->warn('Last error date: ' . date('Y-m-d H:i:s', $info['last_error_date']));
            }

            if (isset($info['max_connections'])) {
                $this->line('Max connections: ' . $info['max_connections']);
            }

            if (! empty($info['allowed_updates'])) {
                $this->line('Allowed updates: ' . implode(', ', $info['allowed_updates']));
            }

            return self::SUCCESS;
        }

        $this->error('Failed to get webhook info');
        return self::FAILURE;
    }
}
