<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\EliminationChallengeResolved;
use App\Models\ChallengeParticipant;
use App\Models\Group;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when an elimination challenge is resolved.
 *
 * Triggers:
 * - elimination_winner: For survivors
 */
class CheckBadgesOnEliminationResolved implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(EliminationChallengeResolved $event): void
    {
        $challenge = $event->challenge;
        $survivors = $event->survivors;

        /** @var Group|null $group */
        $group = $challenge->group;

        if ($group === null) {
            return;
        }

        // Award elimination_winner badge to all survivors
        /** @var ChallengeParticipant $participant */
        foreach ($survivors as $participant) {
            /** @var User|null $user */
            $user = $participant->user;

            if ($user === null) {
                continue;
            }

            $this->badgeService->checkAndAward(
                $user,
                'elimination_winner',
                $group,
                ['challenge_id' => $challenge->id, 'participant_id' => $participant->id]
            );
        }
    }
}
