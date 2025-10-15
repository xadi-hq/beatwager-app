<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | The bot token provided by BotFather when you create your bot.
    | This is used to authenticate API requests to Telegram.
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Username
    |--------------------------------------------------------------------------
    |
    | The username of your bot (without the @ symbol).
    | Used for directing users to start a private chat with the bot.
    |
    */
    'bot_username' => env('TELEGRAM_BOT_USERNAME', 'WagerBot'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the webhook endpoint that receives updates from Telegram.
    | The webhook secret is used to verify that requests are coming from Telegram.
    |
    */
    'webhook' => [
        'url' => env('APP_URL') . '/api/telegram/webhook',
        'secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Telegram API Configuration
    |--------------------------------------------------------------------------
    |
    | Base URL for Telegram Bot API. Normally you don't need to change this
    | unless you're using a local Bot API server.
    |
    */
    'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),

    /*
    |--------------------------------------------------------------------------
    | Bot Commands
    |--------------------------------------------------------------------------
    |
    | List of available bot commands for registration with BotFather.
    |
    */
    'commands' => [
        'start' => 'Initialize bot and show welcome message',
        'newwager' => 'Create a new wager in the group',
        'join' => 'Join an existing wager',
        'mywagers' => 'View your active wagers',
        'balance' => 'Check your points balance',
        'leaderboard' => 'View group leaderboard',
        'help' => 'Show help information',
    ],
];
