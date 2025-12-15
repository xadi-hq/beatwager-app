<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SuperChallengeCompletionValidated;
use App\Models\Challenge;
use App\Models\Group;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when a super challenge completion is validated.
 *
 * Triggers:
 * - super_challenge_won: For validated participants
 */
class CheckBadgesOnSuperChallengeValidated implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(SuperChallengeCompletionValidated $event): void
    {
        // Only award badge if the completion was approved
        if (!$event->approved) {
            return;
        }

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
            'super_challenge_won',
            $group,
            ['challenge_id' => $challenge->id, 'participant_id' => $participant->id]
        );
    }
}
