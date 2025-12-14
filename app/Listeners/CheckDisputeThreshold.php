<?php

namespace App\Listeners;

use App\Enums\DisputeVoteOutcome;
use App\Events\DisputeResolved;
use App\Events\DisputeVoteReceived;
use App\Services\DisputeService;
use App\Services\MessageService;
use App\Services\MessengerFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CheckDisputeThreshold implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 5;

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly DisputeService $disputeService,
        private readonly MessageService $messageService,
        private readonly MessengerFactory $messengerFactory
    ) {}

    /**
     * Handle the event.
     *
     * Checks if the vote threshold has been met and resolves the dispute if so.
     */
    public function handle(DisputeVoteReceived $event): void
    {
        $vote = $event->vote;
        $dispute = $vote->dispute;

        if ($dispute->isResolved()) {
            Log::info('Dispute already resolved, skipping threshold check', [
                'dispute_id' => $dispute->id,
            ]);
            return;
        }

        // Get vote counts by outcome
        $votes = $dispute->votes;
        $voteCounts = $votes->groupBy('vote_outcome')->map->count();

        $originalCorrectCount = $voteCounts->get(DisputeVoteOutcome::OriginalCorrect->value, 0);
        $differentOutcomeCount = $voteCounts->get(DisputeVoteOutcome::DifferentOutcome->value, 0);
        $notYetDeterminableCount = $voteCounts->get(DisputeVoteOutcome::NotYetDeterminable->value, 0);

        $threshold = $dispute->votes_required;

        Log::info('Checking dispute threshold', [
            'dispute_id' => $dispute->id,
            'threshold' => $threshold,
            'original_correct' => $originalCorrectCount,
            'different_outcome' => $differentOutcomeCount,
            'not_yet_determinable' => $notYetDeterminableCount,
        ]);

        // Check if threshold is met for any outcome
        if ($originalCorrectCount >= $threshold) {
            // Original outcome was correct - false dispute
            $this->disputeService->resolveDispute($dispute, DisputeVoteOutcome::OriginalCorrect);
            DisputeResolved::dispatch($dispute->fresh());
        } elseif ($differentOutcomeCount >= $threshold) {
            // Different outcome confirmed - fraud or premature settlement
            $this->disputeService->resolveDispute($dispute, DisputeVoteOutcome::DifferentOutcome);
            DisputeResolved::dispatch($dispute->fresh());
        } elseif ($notYetDeterminableCount >= $threshold) {
            // Premature settlement confirmed
            $this->disputeService->resolveDispute($dispute, DisputeVoteOutcome::NotYetDeterminable);
            DisputeResolved::dispatch($dispute->fresh());
        } else {
            // Threshold not yet met - send anonymous progress notification
            $this->sendVoteProgressNotification($dispute);
        }
    }

    /**
     * Send anonymous vote progress notification to group
     */
    private function sendVoteProgressNotification(\App\Models\Dispute $dispute): void
    {
        try {
            $group = $dispute->group;
            $platform = $group->platform;
            $platformGroupId = $group->platform_group_id;

            if (!$platform || !$platformGroupId) {
                return;
            }

            $message = $this->messageService->disputeVoteProgress($dispute);
            $messenger = $this->messengerFactory->make($platform);
            $messenger->sendMessage($platformGroupId, $message);

        } catch (\Exception $e) {
            Log::warning('Failed to send dispute vote progress notification', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
