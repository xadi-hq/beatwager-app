<?php

namespace App\Exceptions;

/**
 * Thrown when stake amount doesn't match wager requirements
 */
class InvalidStakeException extends BeatWagerException
{
    public function __construct(
        private readonly int $provided,
        private readonly int $required,
    ) {
        parent::__construct(
            "Invalid stake amount. Provided: {$provided}, Required: {$required}"
        );
    }

    public function getProvided(): int
    {
        return $this->provided;
    }

    public function getRequired(): int
    {
        return $this->required;
    }

    public function getUserMessage(): string
    {
        return "The stake amount must be exactly {$this->required} points.";
    }
}
