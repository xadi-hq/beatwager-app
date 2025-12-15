<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\DisputeResolution;
use App\Events\DisputeResolved;
use App\Models\Dispute;
use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Models\WagerEntry;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Check and award badges when a dispute is resolved.
 *
 * Also handles badge revocation when wager results are reversed.
 *
 * Triggers:
 * - fraud_confirmed: For accused users found guilty of fraud
 *
 * Revocations:
 * - wager_won, wager_lost: When wager results are reversed
 */
class CheckBadgesOnDisputeResolved implements ShouldQueue
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    public function handle(DisputeResolved $event): void
    {
        $dispute = $event->dispute;
        $group = $dispute->group;

        // If fraud was confirmed, check fraud badges for the accused
        if ($dispute->resolution === DisputeResolution::FraudConfirmed) {
            $this->badgeService->checkAndAward(
                $dispute->accused,
                'fraud_confirmed',
                $group,
                ['dispute_id' => $dispute->id]
            );
        }

        // Handle badge revocation for reversed wager results
        $this->handleBadgeRevocation($dispute);
    }

    /**
     * Re-check badges when wager results are reversed.
     */
    private function handleBadgeRevocation(Dispute $dispute): void
    {
        // Only handle wager disputes where results might be reversed
        if (!$dispute->disputable instanceof Wager) {
            return;
        }

        // Only proceed if the resolution requires reversal
        if (!$dispute->resolution?->isAccusedAtFault()) {
            return;
        }

        /** @var Wager $wager */
        $wager = $dispute->disputable;

        /** @var Group|null $group */
        $group = $dispute->group;

        if ($group === null) {
            return;
        }

        // Get all participants in the wager
        $entries = WagerEntry::where('wager_id', $wager->id)
            ->with('user')
            ->get();

        // Re-check badges for all affected users
        /** @var WagerEntry $entry */
        foreach ($entries as $entry) {
            /** @var User|null $user */
            $user = $entry->user;

            if ($user === null) {
                continue;
            }

            $this->badgeService->recheckAfterReversal(
                $user,
                'wager_won',
                $group,
                'Dispute resolved - wager results reversed'
            );

            $this->badgeService->recheckAfterReversal(
                $user,
                'wager_lost',
                $group,
                'Dispute resolved - wager results reversed'
            );
        }
    }
}
