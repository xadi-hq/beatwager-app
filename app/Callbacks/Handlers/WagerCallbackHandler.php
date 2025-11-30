<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Events\WagerJoined;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Wager;
use App\Services\UserMessengerService;
use App\Services\WagerService;
use Illuminate\Support\Facades\Log;

/**
 * Handle wager callback - User places a bet on a wager
 */
class WagerCallbackHandler extends AbstractCallbackHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger,
        private readonly WagerService $wagerService
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "wager:{wager_id}:{answer}"
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid wager format',
                showAlert: true
            );
            return;
        }

        // Parse callback data
        $parts = explode(':', $callback->data);
        if (count($parts) !== 2) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid wager format',
                showAlert: true
            );
            return;
        }

        [$wagerId, $answer] = $parts;

        // Find the wager
        $wager = Wager::with(['group'])->find($wagerId);
        if (!$wager) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Wager not found',
                showAlert: true
            );
            return;
        }

        // Check if wager is still open
        if ($wager->status !== 'open') {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ This wager is no longer accepting bets',
                showAlert: true
            );
            return;
        }

        // Check if betting deadline has passed
        if ($wager->betting_closes_at < now()) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Betting deadline has passed',
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
        $group = $wager->group;

        // Ensure user is in the group
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            $group->users()->attach($user->id, [
                'id' => \Illuminate\Support\Str::uuid(),
                'points' => $group->starting_balance ?? 1000,
                'role' => 'participant',
            ]);
        }

        // Check if user already has an entry
        $existingEntry = $wager->entries()->where('user_id', $user->id)->first();
        if ($existingEntry) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '⚠️ You\'ve already placed a bet on this wager',
                showAlert: true
            );
            return;
        }

        // Validate answer based on wager type
        $normalizedAnswer = $this->normalizeAnswer($wager, $answer);
        if ($normalizedAnswer === null) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid answer for this wager',
                showAlert: true
            );
            return;
        }

        try {
            // Place the wager
            $entry = $this->wagerService->placeWager(
                wager: $wager,
                user: $user,
                answerValue: $normalizedAnswer,
                points: $wager->stake_amount
            );

            // Get user's remaining balance
            $groupUser = $group->users()->where('user_id', $user->id)->first();
            $remainingBalance = $groupUser?->pivot?->points ?? 0;

            // Send success message
            $this->messenger->answerCallback(
                $callback->callbackId,
                sprintf(
                    '✅ Bet placed! You wagered %d %s on "%s". Balance: %d',
                    $wager->stake_amount,
                    $group->points_currency_name,
                    $normalizedAnswer,
                    $remainingBalance
                ),
                showAlert: false
            );

            // Dispatch event for LLM-powered announcement
            WagerJoined::dispatch($wager, $entry, $user);

        } catch (\App\Exceptions\UserAlreadyJoinedException $e) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '⚠️ You\'ve already placed a bet on this wager',
                showAlert: true
            );
        } catch (\App\Exceptions\InvalidStakeException $e) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ ' . $e->getMessage(),
                showAlert: true
            );
        } catch (\App\Exceptions\InvalidAnswerException $e) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ ' . $e->getMessage(),
                showAlert: true
            );
        } catch (\Exception $e) {
            Log::error('Error placing wager bet', [
                'error' => $e->getMessage(),
                'wager_id' => $wagerId,
                'user_id' => $callback->userId,
                'answer' => $answer,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error placing bet: ' . $e->getMessage(),
                showAlert: true
            );
        }
    }

    /**
     * Normalize answer based on wager type
     */
    private function normalizeAnswer(Wager $wager, string $answer): ?string
    {
        return match ($wager->type) {
            'binary' => in_array(strtolower($answer), ['yes', 'no']) ? strtolower($answer) : null,
            'multiple_choice' => $this->findMatchingOption($answer, $wager->options ?? []),
            'numeric' => is_numeric($answer) ? $answer : null,
            'date' => $this->validateDate($answer) ? $answer : null,
            default => null,
        };
    }

    /**
     * Find matching option case-insensitively, return original option value
     */
    private function findMatchingOption(string $answer, array $options): ?string
    {
        foreach ($options as $option) {
            if (strtolower($option) === strtolower($answer)) {
                return $option; // Return original casing from options
            }
        }
        return null;
    }

    /**
     * Validate date format
     */
    private function validateDate(string $date): bool
    {
        try {
            \Carbon\Carbon::parse($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getAction(): string
    {
        return 'wager';
    }

    public function getDescription(): string
    {
        return 'Place a bet on a wager';
    }
}
