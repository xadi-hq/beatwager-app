<?php

namespace App\Providers;

use App\Events\ChallengeCreated;
use App\Events\EventCreated;
use App\Events\WagerCreated;
use App\Listeners\SendChallengeAnnouncement;
use App\Listeners\SendEventAnnouncement;
use App\Listeners\SendWagerAnnouncement;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ChallengeCreated::class => [
            SendChallengeAnnouncement::class,
        ],
        EventCreated::class => [
            SendEventAnnouncement::class,
        ],
        WagerCreated::class => [
            SendWagerAnnouncement::class,
        ],
    ];

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
        //
    }
}
