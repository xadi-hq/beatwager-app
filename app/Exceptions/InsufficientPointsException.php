<?php

namespace App\Exceptions;

/**
 * Thrown when a user attempts to wager more points than they have available
 */
class InsufficientPointsException extends BeatWagerException
{
    public function __construct(
        private readonly int $required,
        private readonly int $available,
    ) {
        parent::__construct(
            "Insufficient points. Required: {$required}, Available: {$available}"
        );
    }

    public function getRequired(): int
    {
        return $this->required;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }

    public function getUserMessage(): string
    {
        return "You don't have enough points. You need {$this->required} but only have {$this->available}.";
    }
}
