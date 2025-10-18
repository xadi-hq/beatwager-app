<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupSeason extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'group_id',
        'season_number',
        'started_at',
        'ended_at',
        'is_active',
        'final_leaderboard',
        'stats',
        'highlights',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_active' => 'boolean',
            'final_leaderboard' => 'array',
            'stats' => 'array',
            'highlights' => 'array',
        ];
    }

    /**
     * Get the group this season belongs to
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Check if this season is currently active
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->ended_at === null;
    }

    /**
     * Check if this season has ended
     */
    public function hasEnded(): bool
    {
        return $this->ended_at !== null;
    }

    /**
     * Get duration of the season in days
     */
    public function getDurationInDays(): ?int
    {
        if (!$this->hasEnded()) {
            return null;
        }

        return $this->started_at->diffInDays($this->ended_at);
    }

    /**
     * Get the winner of this season (rank 1 in final leaderboard)
     */
    public function getWinner(): ?array
    {
        if (!$this->final_leaderboard || empty($this->final_leaderboard)) {
            return null;
        }

        return collect($this->final_leaderboard)
            ->where('rank', 1)
            ->first();
    }

    /**
     * Get top N players from final leaderboard
     */
    public function getTopPlayers(int $limit = 3): array
    {
        if (!$this->final_leaderboard) {
            return [];
        }

        return collect($this->final_leaderboard)
            ->sortBy('rank')
            ->take($limit)
            ->values()
            ->toArray();
    }
}
