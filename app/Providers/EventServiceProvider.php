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
use App\Events\DisputeCreated;
use App\Events\DisputeResolved;
use App\Events\DisputeVoteReceived;
use App\Events\EventCancelled;
use App\Events\EventCreated;
use App\Events\SuperChallengeAccepted;
use App\Events\SuperChallengeAutoValidated;
use App\Events\SuperChallengeCompletionClaimed;
use App\Events\SuperChallengeCompletionValidated;
use App\Events\SuperChallengeCreated;
use App\Events\SuperChallengeNudgeSent;
use App\Events\EliminationChallengeActivated;
use App\Events\EliminationChallengeCancelled;
use App\Events\EliminationChallengeCreated;
use App\Events\EliminationChallengeMilestone;
use App\Events\EliminationChallengeResolved;
use App\Events\EliminationChallengeTappedIn;
use App\Events\EliminationChallengeTappedOut;
use App\Events\WagerBettingClosed;
use App\Events\WagerCreated;
use App\Events\WagerJoined;
use App\Events\WagerSettled;
use App\Listeners\CheckDisputeThreshold;
use App\Listeners\SendChallengeAcceptedAnnouncement;
use App\Listeners\SendChallengeAnnouncement;
use App\Listeners\SendChallengeApprovedAnnouncement;
use App\Listeners\SendChallengeCancelledAnnouncement;
use App\Listeners\SendChallengeDeadlineMissedAnnouncement;
use App\Listeners\SendChallengeExpiredAnnouncement;
use App\Listeners\SendChallengeRejectedAnnouncement;
use App\Listeners\SendChallengeSubmittedAnnouncement;
use App\Listeners\SendDisputeNotification;
use App\Listeners\SendDisputeResolutionNotification;
use App\Listeners\SendEventAnnouncement;
use App\Listeners\SendEventCancelledAnnouncement;
use App\Listeners\SendSuperChallengeAcceptedNotification;
use App\Listeners\SendSuperChallengeAnnouncement;
use App\Listeners\SendSuperChallengeAutoValidatedNotification;
use App\Listeners\SendSuperChallengeCompletionClaimedNotification;
use App\Listeners\SendSuperChallengeNudge;
use App\Listeners\SendSuperChallengeValidatedNotification;
use App\Listeners\SendBettingClosedAnnouncement;
use App\Listeners\SendEliminationActivatedAnnouncement;
use App\Listeners\SendEliminationCancelledAnnouncement;
use App\Listeners\SendEliminationChallengeAnnouncement;
use App\Listeners\SendEliminationMilestoneAnnouncement;
use App\Listeners\SendEliminationResolvedAnnouncement;
use App\Listeners\SendEliminationTappedInAnnouncement;
use App\Listeners\SendEliminationTappedOutAnnouncement;
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
        WagerBettingClosed::class => [
            SendBettingClosedAnnouncement::class,
        ],
        SuperChallengeNudgeSent::class => [
            SendSuperChallengeNudge::class,
        ],
        SuperChallengeCreated::class => [
            SendSuperChallengeAnnouncement::class,
        ],
        SuperChallengeAccepted::class => [
            SendSuperChallengeAcceptedNotification::class,
        ],
        SuperChallengeCompletionClaimed::class => [
            SendSuperChallengeCompletionClaimedNotification::class,
        ],
        SuperChallengeCompletionValidated::class => [
            SendSuperChallengeValidatedNotification::class,
        ],
        SuperChallengeAutoValidated::class => [
            SendSuperChallengeAutoValidatedNotification::class,
        ],
        EliminationChallengeCreated::class => [
            SendEliminationChallengeAnnouncement::class,
        ],
        EliminationChallengeTappedIn::class => [
            SendEliminationTappedInAnnouncement::class,
        ],
        EliminationChallengeActivated::class => [
            SendEliminationActivatedAnnouncement::class,
        ],
        EliminationChallengeTappedOut::class => [
            SendEliminationTappedOutAnnouncement::class,
        ],
        EliminationChallengeMilestone::class => [
            SendEliminationMilestoneAnnouncement::class,
        ],
        EliminationChallengeResolved::class => [
            SendEliminationResolvedAnnouncement::class,
        ],
        EliminationChallengeCancelled::class => [
            SendEliminationCancelledAnnouncement::class,
        ],
        DisputeCreated::class => [
            SendDisputeNotification::class,
        ],
        DisputeResolved::class => [
            SendDisputeResolutionNotification::class,
        ],
        DisputeVoteReceived::class => [
            CheckDisputeThreshold::class,
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
