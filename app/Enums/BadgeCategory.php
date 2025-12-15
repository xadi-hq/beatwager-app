<?php

declare(strict_types=1);

namespace App\Enums;

enum BadgeCategory: string
{
    case Wagers = 'wagers';
    case Events = 'events';
    case Challenges = 'challenges';
    case Disputes = 'disputes';
    case Special = 'special';

    public function label(): string
    {
        return match ($this) {
            self::Wagers => 'Wagers',
            self::Events => 'Events',
            self::Challenges => 'Challenges',
            self::Disputes => 'Disputes',
            self::Special => 'Special',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Wagers => '🎲',
            self::Events => '📅',
            self::Challenges => '⚔️',
            self::Disputes => '⚖️',
            self::Special => '⭐',
        };
    }
}
