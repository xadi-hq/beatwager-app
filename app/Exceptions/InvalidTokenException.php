<?php

namespace App\Exceptions;

/**
 * Thrown when a token is invalid, expired, or already used
 */
class InvalidTokenException extends BeatWagerException
{
    public function __construct(
        string $reason = 'Token is invalid or has expired',
    ) {
        parent::__construct($reason);
    }

    public function getUserMessage(): string
    {
        return 'This link has expired or is no longer valid. Please request a new one.';
    }

    public function getStatusCode(): int
    {
        return 401;
    }

    public static function expired(): self
    {
        return new self('Token has expired');
    }

    public static function alreadyUsed(): self
    {
        return new self('Token has already been used');
    }

    public static function notFound(): self
    {
        return new self('Token not found');
    }
}
