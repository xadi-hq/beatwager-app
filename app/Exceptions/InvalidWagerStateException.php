<?php

namespace App\Exceptions;

use App\Models\Wager;

/**
 * Thrown when attempting an operation on a wager in an invalid state
 */
class InvalidWagerStateException extends BeatWagerException
{
    public function __construct(
        private readonly Wager $wager,
        private readonly string $attemptedAction,
        private readonly array $validStatuses,
    ) {
        $validStates = implode(', ', $validStatuses);
        parent::__construct(
            "Cannot {$attemptedAction} wager '{$wager->title}' in status '{$wager->status}'. " .
            "Valid statuses: {$validStates}"
        );
    }

    public function getWager(): Wager
    {
        return $this->wager;
    }

    public function getAttemptedAction(): string
    {
        return $this->attemptedAction;
    }

    public function getValidStatuses(): array
    {
        return $this->validStatuses;
    }

    public function getUserMessage(): string
    {
        return "This action cannot be performed on a wager in '{$this->wager->status}' status.";
    }

    public function getStatusCode(): int
    {
        return 422;
    }
}
