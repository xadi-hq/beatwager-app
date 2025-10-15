<?php

namespace App\Exceptions;

use Exception;

/**
 * Base exception for all BeatWager application exceptions
 */
abstract class BeatWagerException extends Exception
{
    /**
     * Get HTTP status code for this exception
     */
    public function getStatusCode(): int
    {
        return 400;
    }

    /**
     * Get user-friendly error message
     */
    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}
