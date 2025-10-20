<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Telegram BotApi as singleton for dependency injection
        $this->app->singleton(\TelegramBot\Api\BotApi::class, function ($app) {
            $token = config('telegram.bot_token');

            if (empty($token)) {
                throw new \RuntimeException(
                    'Telegram bot token not configured. Set TELEGRAM_BOT_TOKEN in .env'
                );
            }

            return new \TelegramBot\Api\BotApi($token);
        });

        // Register TelegramAdapter as MessengerAdapterInterface
        $this->app->singleton(
            \App\Messaging\MessengerAdapterInterface::class,
            \App\Messaging\Adapters\TelegramAdapter::class
        );

        // Register CommandRegistry with all command handlers
        $this->app->singleton(\App\Commands\CommandRegistry::class, function ($app) {
            $registry = new \App\Commands\CommandRegistry();
            $messenger = $app->make(\App\Messaging\MessengerAdapterInterface::class);

            // Register all command handlers
            $registry->register(new \App\Commands\Handlers\StartCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\HelpCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\NewWagerCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\NewEventCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\NewChallengeCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\JoinCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\MyWagersCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\BalanceCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\LeaderboardCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\WagersCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\ChallengesCommandHandler($messenger));
            $registry->register(new \App\Commands\Handlers\EventsCommandHandler($messenger));

            // Set fallback handler for unknown commands
            $registry->setFallbackHandler(new \App\Commands\Handlers\UnknownCommandHandler($messenger));

            return $registry;
        });

        // Register CallbackRegistry with all callback handlers
        $this->app->singleton(\App\Callbacks\CallbackRegistry::class, function ($app) {
            $registry = new \App\Callbacks\CallbackRegistry();
            $messenger = $app->make(\App\Messaging\MessengerAdapterInterface::class);
            $challengeService = $app->make(\App\Services\ChallengeService::class);

            // Register all callback handlers
            $registry->register(new \App\Callbacks\Handlers\ChallengeAcceptCallbackHandler($messenger, $challengeService));
            $registry->register(new \App\Callbacks\Handlers\ChallengeViewCallbackHandler($messenger));

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
