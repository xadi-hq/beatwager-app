<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Models\Challenge;
use App\Services\SuperChallengeService;
use App\Services\UserMessengerService;
use Illuminate\Support\Facades\Log;

/**
 * Handle superchallenge_accept callback - User accepts a SuperChallenge
 */
class SuperChallengeAcceptCallbackHandler extends AbstractCallbackHandler
{
    public function __construct(
        \App\Messaging\MessengerAdapterInterface $messenger,
        private readonly SuperChallengeService $superChallengeService
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "superchallenge_accept:{challenge_id}"
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid SuperChallenge format',
                showAlert: true
            );
            return;
        }

        $challengeId = $callback->data;

        // Find the challenge
        $challenge = Challenge::find($challengeId);
        if (!$challenge || !$challenge->isSuperChallenge()) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ SuperChallenge not found',
                showAlert: true
            );
            return;
        }

        // Check if challenge is still open
        if ($challenge->status !== 'open') {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ This SuperChallenge is no longer available',
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
            // Accept SuperChallenge using SuperChallengeService
            $this->superChallengeService->acceptChallenge($challenge, $user);

            // Send success message
            $this->messenger->answerCallback(
                $callback->callbackId,
                '💪 SuperChallenge accepted! Good luck!',
                showAlert: false
            );

        } catch (\Exception $e) {
            Log::error('Error accepting SuperChallenge', [
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
        return 'superchallenge_accept';
    }

    public function getDescription(): string
    {
        return 'Accept a SuperChallenge in a group';
    }
}
