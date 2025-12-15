<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ChallengeApproved;
use App\Models\Group;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when a challenge is approved (completed).
 *
 * Triggers:
 * - challenge_given: For the creator who gave out the challenge
 */
class CheckBadgesOnChallengeApproved implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(ChallengeApproved $event): void
    {
        $challenge = $event->challenge;

        /** @var User|null $creator */
        $creator = $challenge->creator;

        /** @var Group|null $group */
        $group = $challenge->group;

        if ($creator === null || $group === null) {
            return;
        }

        // The creator "gave" a challenge that was completed
        $this->badgeService->checkAndAward(
            $creator,
            'challenge_given',
            $group,
            ['challenge_id' => $challenge->id]
        );
    }
}
