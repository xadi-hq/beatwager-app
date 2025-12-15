<?php

declare(strict_types=1);

namespace App\Enums;

enum BadgeCriteriaType: string
{
    case First = 'first';           // First occurrence (e.g., first wager won)
    case Count = 'count';           // Cumulative count threshold (e.g., 5 wagers won)
    case Streak = 'streak';         // Consecutive streak (e.g., 5 events in a row)
    case Comparative = 'comparative'; // Ranking/comparison (e.g., most wagers settled)

    public function label(): string
    {
        return match ($this) {
            self::First => 'First Achievement',
            self::Count => 'Count Threshold',
            self::Streak => 'Streak',
            self::Comparative => 'Comparative',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::First => 'Awarded on first occurrence of an action',
            self::Count => 'Awarded when cumulative count reaches threshold',
            self::Streak => 'Awarded for consecutive completions',
            self::Comparative => 'Awarded based on comparison with others',
        };
    }

    /**
     * Whether this criteria type can be revoked when stats change
     */
    public function isRevocable(): bool
    {
        return match ($this) {
            self::First => false,       // First achievements typically not revoked
            self::Count => true,        // Can fall below threshold
            self::Streak => true,       // Streak can be broken
            self::Comparative => true,  // Can lose ranking
        };
    }
}
