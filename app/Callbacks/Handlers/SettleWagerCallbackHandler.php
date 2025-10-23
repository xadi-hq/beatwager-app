<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Wager;
use App\Services\UserMessengerService;
use App\Services\WagerService;
use Illuminate\Support\Facades\Log;

/**
 * Handle settle_wager callback - Settle a wager with a specific outcome
 *
 * Anyone can settle a wager for faster processing and engagement.
 * settler_id is stored for accountability/audit trail.
 */
class SettleWagerCallbackHandler extends AbstractCallbackHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger,
        private readonly WagerService $wagerService
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "settle_wager:{wager_id}:{outcome}"
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

        [$wagerId, $outcome] = $parts;

        // Find the wager
        $wager = Wager::with(['group', 'creator'])->find($wagerId);
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
                '❌ This wager has already been settled',
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

        // Anyone can settle via button for faster processing
        // settler_id tracks who settled for accountability

        // Validate outcome based on wager type
        $normalizedOutcome = $this->normalizeOutcome($wager, $outcome);
        if ($normalizedOutcome === null) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid outcome for this wager',
                showAlert: true
            );
            return;
        }

        try {
            // Settle the wager
            $this->wagerService->settleWager(
                wager: $wager,
                outcomeValue: $normalizedOutcome,
                settlementNote: null,
                settlerId: $user->id // Track who settled
            );

            // Send success message
            $this->messenger->answerCallback(
                $callback->callbackId,
                sprintf('✅ Wager settled! Outcome: "%s"', $normalizedOutcome),
                showAlert: false
            );

        } catch (\Exception $e) {
            Log::error('Error settling wager via callback', [
                'error' => $e->getMessage(),
                'wager_id' => $wagerId,
                'user_id' => $callback->userId,
                'outcome' => $outcome,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error settling wager: ' . $e->getMessage(),
                showAlert: true
            );
        }
    }

    /**
     * Normalize outcome based on wager type
     */
    private function normalizeOutcome(Wager $wager, string $outcome): ?string
    {
        return match ($wager->type) {
            'binary' => in_array(strtolower($outcome), ['yes', 'no']) ? strtolower($outcome) : null,
            'multiple_choice' => in_array($outcome, $wager->options ?? [])
                ? $outcome
                : $this->findMatchingOption($wager, $outcome),
            'numeric' => is_numeric($outcome) ? $outcome : null,
            'date' => $this->validateDate($outcome) ? $outcome : null,
            default => null,
        };
    }

    /**
     * Try to find matching option for multiple choice (case-insensitive)
     */
    private function findMatchingOption(Wager $wager, string $outcome): ?string
    {
        foreach ($wager->options ?? [] as $option) {
            if (strtolower($option) === strtolower($outcome)) {
                return $option;
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
        return 'settle_wager';
    }

    public function getDescription(): string
    {
        return 'Settle a wager with a specific outcome';
    }
}
