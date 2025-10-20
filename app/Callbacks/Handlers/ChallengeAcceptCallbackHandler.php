<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Models\Challenge;
use App\Services\ChallengeService;
use App\Services\UserMessengerService;
use Illuminate\Support\Facades\Log;

/**
 * Handle challenge_accept callback - User accepts a 1-on-1 challenge
 */
class ChallengeAcceptCallbackHandler extends AbstractCallbackHandler
{
    public function __construct(
        protected readonly \App\Messaging\MessengerAdapterInterface $messenger,
        private readonly ChallengeService $challengeService
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "challenge_accept:{challenge_id}"
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid challenge format',
                showAlert: true
            );
            return;
        }

        $challengeId = $callback->data;

        // Find the challenge
        $challenge = Challenge::find($challengeId);
        if (!$challenge) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Challenge not found',
                showAlert: true
            );
            return;
        }

        // Check if challenge is still open
        if (!$challenge->canBeAccepted()) {
            $message = $challenge->isAcceptanceExpired()
                ? '❌ Challenge acceptance deadline has passed'
                : '❌ This challenge is no longer available';
            $this->messenger->answerCallback(
                $callback->callbackId,
                $message,
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

        // Get the group
        $group = $challenge->group;

        // Ensure user is in the group
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            $group->users()->attach($user->id, [
                'id' => \Illuminate\Support\Str::uuid(),
                'points' => $group->starting_balance ?? 1000,
                'role' => 'participant',
            ]);
        }

        try {
            // Accept challenge using ChallengeService
            $this->challengeService->acceptChallenge($challenge, $user);

            // Send success message
            $this->messenger->answerCallback(
                $callback->callbackId,
                '💪 Challenge accepted! Good luck!',
                showAlert: false
            );

            // TODO: Dispatch event for challenge accepted announcement
            // \App\Events\ChallengeAccepted::dispatch($acceptedChallenge, $user);

        } catch (\Exception $e) {
            Log::error('Error accepting challenge', [
                'error' => $e->getMessage(),
                'challenge_id' => $challengeId,
                'user_id' => $callback->userId,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error: ' . $e->getMessage(),
                showAlert: true
            );
        }
    }

    public function getAction(): string
    {
        return 'challenge_accept';
    }

    public function getDescription(): string
    {
        return 'Accept a 1-on-1 challenge from another user';
    }
}
