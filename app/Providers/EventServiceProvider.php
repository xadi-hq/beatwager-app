<?php

namespace App\Providers;

use App\Events\ChallengeAccepted;
use App\Events\ChallengeApproved;
use App\Events\ChallengeCancelled;
use App\Events\ChallengeCreated;
use App\Events\ChallengeDeadlineMissed;
use App\Events\ChallengeExpired;
use App\Events\ChallengeRejected;
use App\Events\ChallengeSubmitted;
use App\Events\EventCancelled;
use App\Events\EventCreated;
use App\Events\WagerCreated;
use App\Events\WagerJoined;
use App\Events\WagerSettled;
use App\Listeners\SendChallengeAcceptedAnnouncement;
use App\Listeners\SendChallengeAnnouncement;
use App\Listeners\SendChallengeApprovedAnnouncement;
use App\Listeners\SendChallengeCancelledAnnouncement;
use App\Listeners\SendChallengeDeadlineMissedAnnouncement;
use App\Listeners\SendChallengeExpiredAnnouncement;
use App\Listeners\SendChallengeRejectedAnnouncement;
use App\Listeners\SendChallengeSubmittedAnnouncement;
use App\Listeners\SendEventAnnouncement;
use App\Listeners\SendEventCancelledAnnouncement;
use App\Listeners\SendWagerAnnouncement;
use App\Listeners\SendWagerJoinAnnouncement;
use App\Listeners\SendWagerSettlement;
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
        ChallengeAccepted::class => [
            SendChallengeAcceptedAnnouncement::class,
        ],
        ChallengeSubmitted::class => [
            SendChallengeSubmittedAnnouncement::class,
        ],
        ChallengeApproved::class => [
            SendChallengeApprovedAnnouncement::class,
        ],
        ChallengeRejected::class => [
            SendChallengeRejectedAnnouncement::class,
        ],
        ChallengeCancelled::class => [
            SendChallengeCancelledAnnouncement::class,
        ],
        ChallengeExpired::class => [
            SendChallengeExpiredAnnouncement::class,
        ],
        ChallengeDeadlineMissed::class => [
            SendChallengeDeadlineMissedAnnouncement::class,
        ],
        EventCreated::class => [
            SendEventAnnouncement::class,
        ],
        EventCancelled::class => [
            SendEventCancelledAnnouncement::class,
        ],
        WagerCreated::class => [
            SendWagerAnnouncement::class,
        ],
        WagerJoined::class => [
            SendWagerJoinAnnouncement::class,
        ],
        WagerSettled::class => [
            SendWagerSettlement::class,
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
