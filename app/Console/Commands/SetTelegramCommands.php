<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-commands {--clear : Clear all commands instead of setting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set bot commands in Telegram via setMyCommands API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = config('telegram.bot_token');

        if (!$token) {
            $this->error('Telegram bot token not configured in TELEGRAM_BOT_TOKEN');
            return 1;
        }

        $baseUrl = "https://api.telegram.org/bot{$token}";

        if ($this->option('clear')) {
            return $this->clearCommands($baseUrl);
        }

        // Define commands by scope
        // Telegram BotCommandScope types: default, all_private_chats, all_group_chats, all_chat_administrators
        // Order matters: set default first, then more specific scopes override it
        $commandsByScope = [
            // Default scope (fallback) - basic commands only
            'default' => [
                ['command' => 'start', 'description' => 'Initialize the bot'],
                ['command' => 'help', 'description' => 'Show available commands'],
            ],
            // Private chat commands (1:1 with bot)
            'all_private_chats' => [
                ['command' => 'start', 'description' => 'Initialize the bot and get started'],
                ['command' => 'login', 'description' => 'Get a login link to access the webapp'],
                ['command' => 'help', 'description' => 'Show available commands and how to use them'],
                ['command' => 'donate', 'description' => 'Donate points to another user'],
                ['command' => 'mywagers', 'description' => 'View your active wagers'],
                ['command' => 'balance', 'description' => 'Check your points balance'],
            ],
            // Group chat commands
            'all_group_chats' => [
                ['command' => 'start', 'description' => 'Initialize the bot'],
                ['command' => 'login', 'description' => 'Get a login link to access the webapp'],
                ['command' => 'help', 'description' => 'Show available commands'],
                ['command' => 'newwager', 'description' => 'Create a new wager'],
                ['command' => 'newchallenge', 'description' => 'Create a new challenge'],
                ['command' => 'newevent', 'description' => 'Create a new event'],
                ['command' => 'join', 'description' => 'Join an existing wager'],
                ['command' => 'mywagers', 'description' => 'View your active wagers'],
                ['command' => 'balance', 'description' => 'Check your points balance'],
                ['command' => 'leaderboard', 'description' => 'View group leaderboard'],
                ['command' => 'wagers', 'description' => 'List all group wagers'],
                ['command' => 'challenges', 'description' => 'List all group challenges'],
                ['command' => 'events', 'description' => 'List all group events'],
                ['command' => 'settle', 'description' => 'Settle a wager with a result'],
            ],
        ];

        // Language codes to set commands for
        $languages = ['en', 'nl', 'es', 'fr', 'de', 'it', 'pt'];

        // Set commands for each scope
        foreach ($commandsByScope as $scopeType => $commands) {
            $scope = ['type' => $scopeType];

            $this->info("Setting commands for scope: {$scopeType}...");

            $response = Http::post("{$baseUrl}/setMyCommands", [
                'commands' => $commands,
                'scope' => $scope,
            ]);

            if (!$response->successful() || !$response->json('ok')) {
                $this->error("Failed to set {$scopeType} commands: " . $response->json('description'));
                return 1;
            }

            $this->info("✓ Commands set for {$scopeType} scope");

            // Set language-specific commands for each scope
            foreach ($languages as $lang) {
                if ($lang === 'en') {
                    continue; // Already set as default
                }

                $this->info("  Setting {$scopeType} commands for language: {$lang}...");

                $response = Http::post("{$baseUrl}/setMyCommands", [
                    'commands' => $commands,
                    'scope' => $scope,
                    'language_code' => $lang,
                ]);

                if (!$response->successful() || !$response->json('ok')) {
                    $this->warn("  Failed to set {$scopeType} commands for {$lang}: " . $response->json('description'));
                    continue;
                }

                $this->info("  ✓ Commands set for {$scopeType}/{$lang}");
            }
        }

        $this->newLine();
        $this->info('🎉 All Telegram commands have been set successfully!');
        $this->info('Users can now see the command menu when typing / in Telegram.');

        return 0;
    }

    private function clearCommands(string $baseUrl): int
    {
        $this->info('Clearing all Telegram commands...');

        $response = Http::post("{$baseUrl}/deleteMyCommands");

        if (!$response->successful() || !$response->json('ok')) {
            $this->error('Failed to clear commands: ' . $response->json('description'));
            return 1;
        }

        $this->info('✓ All commands cleared successfully');
        return 0;
    }
}
