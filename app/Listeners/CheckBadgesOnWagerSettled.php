<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\WagerSettled;
use App\Models\Group;
use App\Models\User;
use App\Models\WagerEntry;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when a wager is settled.
 *
 * Triggers:
 * - wager_won: For winning participants
 * - wager_lost: For losing participants
 * - wager_settled: For the settler
 */
class CheckBadgesOnWagerSettled implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(WagerSettled $event): void
    {
        $wager = $event->wager;

        /** @var Group|null $group */
        $group = $wager->group;

        if ($group === null) {
            return;
        }

        // Check badges for winners
        /** @var WagerEntry $entry */
        foreach ($wager->entries()->where('is_winner', true)->with('user')->get() as $entry) {
            /** @var User|null $user */
            $user = $entry->user;
            if ($user !== null) {
                $this->badgeService->checkAndAward(
                    $user,
                    'wager_won',
                    $group,
                    ['wager_id' => $wager->id]
                );
            }
        }

        // Check badges for losers
        /** @var WagerEntry $entry */
        foreach ($wager->entries()->where('is_winner', false)->whereNotNull('result')->with('user')->get() as $entry) {
            /** @var User|null $user */
            $user = $entry->user;
            if ($user !== null) {
                $this->badgeService->checkAndAward(
                    $user,
                    'wager_lost',
                    $group,
                    ['wager_id' => $wager->id]
                );
            }
        }

        // Check badges for the settler (if exists and wager had participants)
        // Don't count settling a wager with no entries - that's just cleanup, not a real settlement
        /** @var User|null $settler */
        $settler = $wager->settler;
        $hadParticipants = $wager->entries()->exists();
        if ($wager->settler_id !== null && $settler !== null && $hadParticipants) {
            $this->badgeService->checkAndAward(
                $settler,
                'wager_settled',
                $group,
                ['wager_id' => $wager->id]
            );
        }
    }
}
