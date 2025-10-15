<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class HttpsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Force HTTPS URL generation when behind ngrok or other HTTPS proxy
        if ($this->app->environment('local')) {
            if (request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
                URL::forceScheme('https');
            }
        }
    }
}
