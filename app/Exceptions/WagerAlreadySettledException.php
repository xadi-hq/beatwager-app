<?php

namespace App\Exceptions;

use App\Models\Wager;

/**
 * Thrown when attempting to settle an already settled wager
 */
class WagerAlreadySettledException extends BeatWagerException
{
    public function __construct(
        private readonly Wager $wager,
    ) {
        parent::__construct(
            "Wager '{$wager->title}' has already been settled at {$wager->settled_at}"
        );
    }

    public function getWager(): Wager
    {
        return $this->wager;
    }

    public function getUserMessage(): string
    {
        return "This wager has already been settled and cannot be changed.";
    }

    public function getStatusCode(): int
    {
        return 422;
    }
}
