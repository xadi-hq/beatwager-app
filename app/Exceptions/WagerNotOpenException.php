<?php

namespace App\Exceptions;

use App\Models\Wager;

/**
 * Thrown when attempting to join a wager that is not in 'open' status
 */
class WagerNotOpenException extends BeatWagerException
{
    public function __construct(
        private readonly Wager $wager,
    ) {
        parent::__construct(
            "Wager '{$wager->title}' is not open for entries. Current status: {$wager->status}"
        );
    }

    public function getWager(): Wager
    {
        return $this->wager;
    }

    public function getUserMessage(): string
    {
        return match ($this->wager->status) {
            'locked' => 'This wager has been locked and is no longer accepting entries.',
            'settled' => 'This wager has already been settled.',
            'cancelled' => 'This wager has been cancelled.',
            'disputed' => 'This wager is currently under dispute.',
            default => 'This wager is not currently accepting entries.',
        };
    }
}
