<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wager extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'group_id',
        'creator_id',
        'title',
        'description',
        'resolution_criteria',
        'type',
        'options',
        'numeric_min',
        'numeric_max',
        'numeric_winner_type',
        'date_min',
        'date_max',
        'date_winner_type',
        'stake_amount',
        'deadline',
        'locked_at',
        'settled_at',
        'status',
        'outcome_value',
        'settlement_note',
        'settler_id',
        'total_points_wagered',
        'participants_count',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'deadline' => 'datetime',
            'locked_at' => 'datetime',
            'settled_at' => 'datetime',
            'date_min' => 'date',
            'date_max' => 'date',
            'total_points_wagered' => 'integer',
            'participants_count' => 'integer',
            'stake_amount' => 'integer',
            'numeric_min' => 'integer',
            'numeric_max' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function settler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settler_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(WagerEntry::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function oneTimeTokens(): HasMany
    {
        return $this->hasMany(OneTimeToken::class);
    }

    /**
     * Check if wager is binary (yes/no)
     */
    public function isBinary(): bool
    {
        return $this->type === 'binary';
    }

    /**
     * Check if wager is multiple choice
     */
    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    /**
     * Check if wager is numeric
     */
    public function isNumeric(): bool
    {
        return $this->type === 'numeric';
    }

    /**
     * Check if wager is date-based
     */
    public function isDate(): bool
    {
        return $this->type === 'date';
    }

    /**
     * Get display options for the wager
     */
    public function getDisplayOptions(): array
    {
        return match ($this->type) {
            'binary' => ['Yes', 'No'],
            'multiple_choice' => $this->options ?? [],
            'numeric' => ['Enter a number'],
            'date' => ['Select a date'],
            default => [],
        };
    }
}
