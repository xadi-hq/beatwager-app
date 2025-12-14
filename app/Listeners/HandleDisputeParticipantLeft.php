<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Events\DisputeResolved;
use App\Models\Dispute;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Handle disputes when a participant (reporter or accused) leaves the group.
 *
 * Behavior:
 * - Accused leaves: Auto-resolve as OriginalCorrect (dispute dismissed, can't penalize)
 * - Reporter leaves: Dispute continues (they already filed it, others can vote)
 * - Voter leaves: Their existing vote still counts (no action needed)
 */
class HandleDisputeParticipantLeft
{
    /**
     * Handle user leaving a group.
     * Called from TelegramWebhookController::removeUserFromGroup or similar.
     */
    public function handle(Group $group, User $user): void
    {
        // Find any active disputes where this user is the accused
        $disputesAsAccused = Dispute::query()
            ->where('group_id', $group->id)
            ->where('accused_id', $user->id)
            ->pending()
            ->get();

        foreach ($disputesAsAccused as $dispute) {
            $this->dismissDispute($dispute, $user, 'accused');
        }

        // Log if user was a reporter (dispute continues, just informational)
        $disputesAsReporter = Dispute::query()
            ->where('group_id', $group->id)
            ->where('reporter_id', $user->id)
            ->pending()
            ->count();

        if ($disputesAsReporter > 0) {
            Log::info('Dispute reporter left group, dispute continues', [
                'user_id' => $user->id,
                'group_id' => $group->id,
                'active_disputes_as_reporter' => $disputesAsReporter,
            ]);
        }
    }

    /**
     * Dismiss a dispute when the accused leaves the group.
     */
    private function dismissDispute(Dispute $dispute, User $user, string $role): void
    {
        Log::info('Dismissing dispute due to participant leaving group', [
            'dispute_id' => $dispute->id,
            'user_id' => $user->id,
            'role' => $role,
        ]);

        // Resolve as OriginalCorrect (dispute dismissed)
        $dispute->update([
            'status' => DisputeStatus::Resolved,
            'resolution' => DisputeResolution::OriginalCorrect,
            'resolved_at' => now(),
        ]);

        // Clear dispute from the item
        $item = $dispute->disputable;
        if ($item) {
            $item->update(['dispute_id' => null]);

            // Restore item status if it was marked as disputed
            if (method_exists($item, 'getAttribute') && $item->status === 'disputed') {
                $item->update(['status' => 'settled']);
            }
        }

        // Fire resolution event (will send notification)
        event(new DisputeResolved($dispute));
    }
}
