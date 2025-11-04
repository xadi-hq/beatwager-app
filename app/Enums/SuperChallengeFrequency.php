<?php

declare(strict_types=1);

namespace App\Enums;

enum SuperChallengeFrequency: string
{
    case OFF = 'off';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';

    public function toInterval(): ?string
    {
        return match($this) {
            self::OFF => null,
            self::WEEKLY => '7 days',
            self::MONTHLY => '1 month',
            self::QUARTERLY => '3 months',
        };
    }
}
