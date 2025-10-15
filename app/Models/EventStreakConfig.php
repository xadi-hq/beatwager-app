<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventStreakConfig extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'group_event_streak_configs';

    protected $fillable = [
        'group_id',
        'enabled',
        'multiplier_tiers',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'multiplier_tiers' => 'array',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the multiplier for a given streak count
     */
    public function getMultiplierForStreak(int $streak): float
    {
        if (!$this->enabled || !$this->multiplier_tiers) {
            return 1.0;
        }

        foreach ($this->multiplier_tiers as $tier) {
            $min = $tier['min'];
            $max = $tier['max'] ?? PHP_INT_MAX;

            if ($streak >= $min && $streak <= $max) {
                return (float) $tier['multiplier'];
            }
        }

        return 1.0; // Fallback
    }

    /**
     * Get the next tier milestone for a given streak
     */
    public function getNextMilestone(int $currentStreak): ?array
    {
        if (!$this->enabled || !$this->multiplier_tiers) {
            return null;
        }

        foreach ($this->multiplier_tiers as $tier) {
            if ($currentStreak < $tier['min']) {
                return [
                    'streak_needed' => $tier['min'],
                    'multiplier' => $tier['multiplier'],
                    'events_remaining' => $tier['min'] - $currentStreak,
                ];
            }
        }

        return null; // Already at max tier
    }
}
