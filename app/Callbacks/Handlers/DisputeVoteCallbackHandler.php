<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Enums\DisputeVoteOutcome;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Dispute;
use App\Models\ShortUrl;
use App\Services\DisputeService;
use App\Services\UserMessengerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Handle dv (dispute vote) callback - User votes on a dispute
 *
 * Callback data format: "{dispute_id}:{vote_type}" or "{dispute_id}:{vote_type}:{selected_outcome}"
 * - dispute_id: UUID of the dispute
 * - vote_type: oc (original_correct), do (different_outcome), te (too_early), v (view)
 * - selected_outcome: (optional) the correct outcome for different_outcome votes
 */
class DisputeVoteCallbackHandler extends AbstractCallbackHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger,
        private readonly DisputeService $disputeService
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingCallback $callback): void
    {
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid vote format',
                showAlert: true
            );
            return;
        }

        // Parse callback data: "{dispute_id}:{vote_type}" or "{dispute_id}:{vote_type}:{outcome}"
        $parts = explode(':', $callback->data);
        if (count($parts) < 2) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid vote format',
                showAlert: true
            );
            return;
        }

        $disputeId = $parts[0];
        $voteType = $parts[1];
        $selectedOutcome = $parts[2] ?? null;

        // Find the dispute
        $dispute = Dispute::with(['group', 'disputable', 'reporter', 'accused'])->find($disputeId);
        if (!$dispute) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Dispute not found',
                showAlert: true
            );
            return;
        }

        // Check if dispute is still pending
        if (!$dispute->isPending()) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '⚠️ This dispute has already been resolved',
                showAlert: true
            );
            return;
        }

        // Check if expired
        if ($dispute->isExpired()) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '⏰ This dispute has expired',
                showAlert: true
            );
            return;
        }

        // Get or create user
        $user = UserMessengerService::findOrCreate(
            platform: $callback->platform,
            platformUserId: $callback->userId,
            userData: [
                'username' => $callback->username,
                'first_name' => $callback->firstName,
                'last_name' => $callback->lastName,
            ]
        );

        // Check if user can vote
        if (!$dispute->canUserVote($user)) {
            // Check specific reason
            if ($user->id === $dispute->reporter_id) {
                $this->messenger->answerCallback(
                    $callback->callbackId,
                    '⚠️ You cannot vote on disputes you reported',
                    showAlert: true
                );
            } elseif ($user->id === $dispute->accused_id) {
                $this->messenger->answerCallback(
                    $callback->callbackId,
                    '⚠️ You cannot vote on disputes where you are accused',
                    showAlert: true
                );
            } elseif ($dispute->votes()->where('voter_id', $user->id)->exists()) {
                $this->messenger->answerCallback(
                    $callback->callbackId,
                    '⚠️ You have already voted on this dispute',
                    showAlert: true
                );
            } else {
                $this->messenger->answerCallback(
                    $callback->callbackId,
                    '⚠️ You are not eligible to vote on this dispute',
                    showAlert: true
                );
            }
            return;
        }

        // Handle view action - redirect to web UI
        if ($voteType === 'v') {
            $this->redirectToWebVoting($callback, $dispute, 'view');
            return;
        }

        // Map vote type to enum (shortened: oc, do, te)
        $outcome = match ($voteType) {
            'oc', 'original_correct' => DisputeVoteOutcome::OriginalCorrect,
            'do', 'different_outcome' => DisputeVoteOutcome::DifferentOutcome,
            'te', 'not_yet_determinable' => DisputeVoteOutcome::NotYetDeterminable,
            default => null,
        };

        if ($outcome === null) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid vote type',
                showAlert: true
            );
            return;
        }

        // For different_outcome votes, we need a selected outcome
        // If not provided inline, redirect to web UI for selection
        if ($outcome === DisputeVoteOutcome::DifferentOutcome && !$selectedOutcome) {
            $this->redirectToWebVoting($callback, $dispute, 'different_outcome');
            return;
        }

        try {
            // Cast the vote
            $this->disputeService->castVote($dispute, $user, $outcome, $selectedOutcome);

            // Get updated vote counts
            $dispute->refresh();
            $voteCount = $dispute->getVoteCount();
            $votesNeeded = $dispute->getRemainingVotesNeeded();

            $message = match ($outcome) {
                DisputeVoteOutcome::OriginalCorrect => '✅ You voted: Original outcome is correct',
                DisputeVoteOutcome::DifferentOutcome => "❌ You voted: Different outcome" . ($selectedOutcome ? " ({$selectedOutcome})" : ''),
                DisputeVoteOutcome::NotYetDeterminable => '⏳ You voted: Too early to determine',
            };

            if ($dispute->isResolved()) {
                $message .= "\n\n🎯 Dispute has been resolved!";
            } else {
                $message .= "\n\n📊 Votes: {$voteCount}/{$dispute->votes_required}";
                if ($votesNeeded > 0) {
                    $message .= " ({$votesNeeded} more needed)";
                }
            }

            $this->messenger->answerCallback(
                $callback->callbackId,
                $message,
                showAlert: true
            );

        } catch (\InvalidArgumentException $e) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ ' . $e->getMessage(),
                showAlert: true
            );
        } catch (\Exception $e) {
            Log::error('Error casting dispute vote', [
                'error' => $e->getMessage(),
                'dispute_id' => $disputeId,
                'user_id' => $callback->userId,
                'vote_type' => $voteType,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error casting vote. Please try again.',
                showAlert: true
            );
        }
    }

    /**
     * Redirect user to web UI for selecting the correct outcome
     */
    private function redirectToWebVoting(IncomingCallback $callback, Dispute $dispute, string $voteType): void
    {
        $botUsername = config('telegram.bot_username', 'WagerBot');

        try {
            // Generate signed URL with encrypted user ID
            $signedUrl = URL::temporarySignedRoute(
                'disputes.show',
                now()->addDays(7),
                [
                    'dispute' => $dispute->id,
                    'u' => encrypt($callback->platform . ':' . $callback->userId),
                    'vote' => $voteType,
                ]
            );

            // Create short URL
            $shortCode = ShortUrl::generateUniqueCode(6);
            ShortUrl::create([
                'code' => $shortCode,
                'target_url' => $signedUrl,
                'expires_at' => now()->addDays(7),
            ]);

            $shortUrlFull = url('/l/' . $shortCode);

            // Send short URL as DM
            $disputableTitle = $dispute->disputable->title ?? $dispute->disputable->name ?? 'Unknown';
            $dmMessage = "🗳️ *Vote on Dispute*\n\n";
            $dmMessage .= "To select the correct outcome, please use the web interface:\n\n";
            $dmMessage .= $shortUrlFull;

            $this->messenger->sendDirectMessage(
                $callback->userId,
                OutgoingMessage::markdown($callback->chatId, $dmMessage)
            );

            $this->messenger->answerCallback(
                $callback->callbackId,
                '✅ Voting link sent to your private chat!'
            );

        } catch (\Exception $e) {
            Log::info('User has not started bot chat for dispute voting', [
                'user_id' => $callback->userId,
                'error' => $e->getMessage(),
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                "Please start a chat with @{$botUsername} first and send /start, then try again!",
                showAlert: true
            );
        }
    }

    public function getAction(): string
    {
        return 'dv';
    }

    public function getDescription(): string
    {
        return 'Vote on a dispute';
    }
}
