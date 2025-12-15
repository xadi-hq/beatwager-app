<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EventCreated;
use App\Models\Group;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when an event is created.
 *
 * Triggers:
 * - event_created: For the event creator
 */
class CheckBadgesOnEventCreated implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(EventCreated $event): void
    {
        $groupEvent = $event->event;

        /** @var User|null $creator */
        $creator = $groupEvent->creator;

        /** @var Group|null $group */
        $group = $groupEvent->group;

        if ($creator === null || $group === null) {
            return;
        }

        $this->badgeService->checkAndAward(
            $creator,
            'event_created',
            $group,
            ['event_id' => $groupEvent->id]
        );
    }
}
