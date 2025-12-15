<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EliminationChallengeTappedOut;
use App\Models\Challenge;
use App\Models\Group;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when a user taps out of an elimination challenge.
 *
 * Triggers:
 * - elimination_tap_out: For users who tapped out
 */
class CheckBadgesOnEliminationTappedOut implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(EliminationChallengeTappedOut $event): void
    {
        $participant = $event->participant;

        /** @var Challenge|null $challenge */
        $challenge = $participant->challenge;

        if ($challenge === null) {
            return;
        }

        /** @var User|null $user */
        $user = $participant->user;

        /** @var Group|null $group */
        $group = $challenge->group;

        if ($user === null || $group === null) {
            return;
        }

        $this->badgeService->checkAndAward(
            $user,
            'elimination_tap_out',
            $group,
            ['challenge_id' => $challenge->id, 'participant_id' => $participant->id]
        );
    }
}
