<?php

namespace App\Exceptions;

use App\Models\User;
use App\Models\Wager;

/**
 * Thrown when a user attempts to join a wager they've already joined
 */
class UserAlreadyJoinedException extends BeatWagerException
{
    public function __construct(
        private readonly User $user,
        private readonly Wager $wager,
    ) {
        parent::__construct(
            "User '{$user->name}' has already joined wager '{$wager->title}'"
        );
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getWager(): Wager
    {
        return $this->wager;
    }

    public function getUserMessage(): string
    {
        return "You've already placed a bet on this wager.";
    }

    public function getStatusCode(): int
    {
        return 422;
    }
}
