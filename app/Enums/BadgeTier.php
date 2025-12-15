<?php

declare(strict_types=1);

namespace App\Enums;

enum BadgeTier: string
{
    case Standard = 'standard';
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Platinum = 'platinum';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Bronze => 'Bronze',
            self::Silver => 'Silver',
            self::Gold => 'Gold',
            self::Platinum => 'Platinum',
        };
    }

    /**
     * Border color for the badge tier
     */
    public function color(): string
    {
        return match ($this) {
            self::Standard => '#9E9E9E',
            self::Bronze => '#CD7F32',
            self::Silver => '#C0C0C0',
            self::Gold => '#FFD700',
            self::Platinum => '#E5E4E2',
        };
    }

    /**
     * Typical threshold for this tier (0 for standard/first achievements)
     */
    public function typicalThreshold(): int
    {
        return match ($this) {
            self::Standard => 0,
            self::Bronze => 5,
            self::Silver => 10,
            self::Gold => 20,
            self::Platinum => 50,
        };
    }

    /**
     * Sort order for displaying tiers (lowest to highest)
     */
    public function sortOrder(): int
    {
        return match ($this) {
            self::Standard => 1,
            self::Bronze => 2,
            self::Silver => 3,
            self::Gold => 4,
            self::Platinum => 5,
        };
    }
}
