<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\WagerCreated;
use App\Models\Group;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when a wager is created.
 *
 * Triggers:
 * - wager_created: For the wager creator
 */
class CheckBadgesOnWagerCreated implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(WagerCreated $event): void
    {
        $wager = $event->wager;

        /** @var User|null $creator */
        $creator = $wager->creator;

        /** @var Group|null $group */
        $group = $wager->group;

        if ($creator === null || $group === null) {
            return;
        }

        $this->badgeService->checkAndAward(
            $creator,
            'wager_created',
            $group,
            ['wager_id' => $wager->id]
        );
    }
}
