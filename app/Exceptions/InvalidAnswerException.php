<?php

namespace App\Exceptions;

/**
 * Thrown when an answer doesn't match the wager type requirements
 */
class InvalidAnswerException extends BeatWagerException
{
    public function __construct(
        string $message,
        private readonly ?string $wagerType = null,
    ) {
        parent::__construct($message);
    }

    public function getWagerType(): ?string
    {
        return $this->wagerType;
    }

    public function getUserMessage(): string
    {
        return $this->getMessage();
    }

    public static function forBinary(string $provided): self
    {
        return new self(
            "Binary answer must be 'yes' or 'no'. Provided: '{$provided}'",
            'binary'
        );
    }

    public static function forMultipleChoice(string $provided, array $validOptions): self
    {
        $options = implode(', ', $validOptions);
        return new self(
            "Answer must be one of: {$options}. Provided: '{$provided}'",
            'multiple_choice'
        );
    }

    public static function forNumeric(string $provided, ?int $min = null, ?int $max = null): self
    {
        $constraints = [];
        if ($min !== null) {
            $constraints[] = "at least {$min}";
        }
        if ($max !== null) {
            $constraints[] = "at most {$max}";
        }

        $constraint = !empty($constraints) ? ' (' . implode(' and ', $constraints) . ')' : '';
        return new self(
            "Answer must be a valid integer{$constraint}. Provided: '{$provided}'",
            'numeric'
        );
    }

    public static function forDate(string $provided, ?string $min = null, ?string $max = null): self
    {
        $constraints = [];
        if ($min !== null) {
            $constraints[] = "on or after {$min}";
        }
        if ($max !== null) {
            $constraints[] = "on or before {$max}";
        }

        $constraint = !empty($constraints) ? ' (' . implode(' and ', $constraints) . ')' : '';
        return new self(
            "Answer must be a valid date in format YYYY-MM-DD{$constraint}. Provided: '{$provided}'",
            'date'
        );
    }
}
