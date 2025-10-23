<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Challenge;
use App\Services\ChallengeService;
use App\Services\UserMessengerService;
use Illuminate\Support\Facades\Log;

/**
 * Handle settle_challenge callback - Approve or reject a challenge completion
 */
class SettleChallengeCallbackHandler extends AbstractCallbackHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger,
        private readonly ChallengeService $challengeService
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "settle_challenge:{challenge_id}:{yes|no}"
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid format',
                showAlert: true
            );
            return;
        }

        // Parse callback data
        $parts = explode(':', $callback->data);
        if (count($parts) !== 2) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid format',
                showAlert: true
            );
            return;
        }

        [$challengeId, $decision] = $parts;

        // Validate decision
        if (!in_array(strtolower($decision), ['yes', 'no'])) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid decision',
                showAlert: true
            );
            return;
        }

        // Find the challenge
        $challenge = Challenge::with(['group', 'creator', 'acceptor'])->find($challengeId);
        if (!$challenge) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Challenge not found',
                showAlert: true
            );
            return;
        }

        // Check if challenge is in accepted status (past deadline)
        if ($challenge->status !== 'accepted') {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ This challenge cannot be settled at this time',
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

        // Determine expected approver (payer)
        $expectedApprover = $challenge->getPayer();

        // Allow creator to settle if it's past the deadline
        if ($challenge->creator_id !== $user->id && $expectedApprover->id !== $user->id) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '⚠️ Only the challenge creator or payer can settle this challenge',
                showAlert: true
            );
            return;
        }

        try {
            if (strtolower($decision) === 'yes') {
                // Mark as submitted first if not already
                if (!$challenge->submitted_at) {
                    $challenge->update([
                        'submitted_at' => now(),
                        'submission_note' => 'Marked as completed via callback',
                    ]);
                }

                // Approve the challenge
                $this->challengeService->approveChallenge($challenge, $expectedApprover);

                $this->messenger->answerCallback(
                    $callback->callbackId,
                    '✅ Challenge approved! Points transferred.',
                    showAlert: false
                );

            } else {
                // Reject the challenge
                $this->challengeService->rejectChallenge(
                    $challenge,
                    $expectedApprover,
                    'Challenge deadline passed without completion'
                );

                $this->messenger->answerCallback(
                    $callback->callbackId,
                    '❌ Challenge marked as failed. Points returned.',
                    showAlert: false
                );
            }

        } catch (\Exception $e) {
            Log::error('Error settling challenge via callback', [
                'error' => $e->getMessage(),
                'challenge_id' => $challengeId,
                'user_id' => $callback->userId,
                'decision' => $decision,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error settling challenge: ' . $e->getMessage(),
                showAlert: true
            );
        }
    }

    public function getAction(): string
    {
        return 'settle_challenge';
    }

    public function getDescription(): string
    {
        return 'Approve or reject a challenge completion';
    }
}
