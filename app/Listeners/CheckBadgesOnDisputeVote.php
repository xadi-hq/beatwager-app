<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DisputeVoteReceived;
use App\Models\Dispute;
use App\Models\Group;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when a dispute vote is cast.
 *
 * Triggers:
 * - dispute_judged: For users who vote on disputes
 */
class CheckBadgesOnDisputeVote implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(DisputeVoteReceived $event): void
    {
        $vote = $event->vote;

        /** @var User|null $voter */
        $voter = $vote->voter;

        /** @var Dispute|null $dispute */
        $dispute = $vote->dispute;

        if ($voter === null || $dispute === null) {
            return;
        }

        /** @var Group|null $group */
        $group = $dispute->group;

        if ($group === null) {
            return;
        }

        $this->badgeService->checkAndAward(
            $voter,
            'dispute_judged',
            $group,
            ['dispute_id' => $dispute->id, 'vote_id' => $vote->id]
        );
    }
}
