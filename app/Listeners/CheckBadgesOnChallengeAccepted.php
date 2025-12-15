<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ChallengeAccepted;
use App\Models\Group;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when a challenge is accepted.
 *
 * Triggers:
 * - challenge_requested: For the acceptor who took on the challenge
 */
class CheckBadgesOnChallengeAccepted implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(ChallengeAccepted $event): void
    {
        $challenge = $event->challenge;
        $acceptor = $event->acceptor;

        /** @var Group|null $group */
        $group = $challenge->group;

        if ($group === null) {
            return;
        }

        $this->badgeService->checkAndAward(
            $acceptor,
            'challenge_requested',
            $group,
            ['challenge_id' => $challenge->id]
        );
    }
}
