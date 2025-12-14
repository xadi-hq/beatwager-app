<?php

declare(strict_types=1);

namespace App\Enums;

enum DisputeResolution: string
{
    case OriginalCorrect = 'original_correct';
    case FraudConfirmed = 'fraud_confirmed';
    case PrematureSettlement = 'premature_settlement';

    public function label(): string
    {
        return match ($this) {
            self::OriginalCorrect => 'Original Correct',
            self::FraudConfirmed => 'Fraud Confirmed',
            self::PrematureSettlement => 'Premature Settlement',
        };
    }

    /**
     * Whether this resolution indicates the accused was at fault.
     */
    public function isAccusedAtFault(): bool
    {
        return match ($this) {
            self::OriginalCorrect => false,
            self::FraudConfirmed => true,
            self::PrematureSettlement => true,
        };
    }
}
