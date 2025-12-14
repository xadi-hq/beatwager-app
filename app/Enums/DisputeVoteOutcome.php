<?php

declare(strict_types=1);

namespace App\Enums;

enum DisputeVoteOutcome: string
{
    case OriginalCorrect = 'original_correct';
    case DifferentOutcome = 'different_outcome';
    case NotYetDeterminable = 'not_yet_determinable';

    public function label(): string
    {
        return match ($this) {
            self::OriginalCorrect => 'Original outcome is correct',
            self::DifferentOutcome => 'Different outcome',
            self::NotYetDeterminable => 'Outcome not yet determinable',
        };
    }

    /**
     * Map vote outcome to dispute resolution.
     */
    public function toResolution(): DisputeResolution
    {
        return match ($this) {
            self::OriginalCorrect => DisputeResolution::OriginalCorrect,
            self::DifferentOutcome => DisputeResolution::FraudConfirmed,
            self::NotYetDeterminable => DisputeResolution::PrematureSettlement,
        };
    }
}
