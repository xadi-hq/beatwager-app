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
        'betting_closes_at',
        'expected_settlement_at',
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
            'betting_closes_at' => 'datetime',
            'expected_settlement_at' => 'datetime',
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

    /**
     * Check if betting is still open (before betting_closes_at)
     */
    public function isBettingOpen(): bool
    {
        return $this->status === 'open' && $this->betting_closes_at > now();
    }

    /**
     * Check if betting deadline has passed
     */
    public function isPastBettingDeadline(): bool
    {
        return $this->betting_closes_at < now();
    }

    /**
     * Check if wager has an expected settlement date
     */
    public function hasExpectedSettlement(): bool
    {
        return $this->expected_settlement_at !== null;
    }

    /**
     * Get the date that should trigger settlement reminders
     */
    public function getSettlementTriggerDate(): \Carbon\Carbon
    {
        return $this->expected_settlement_at ?? $this->betting_closes_at;
    }

    /**
     * Check if wager is past its settlement trigger date
     */
    public function isPastSettlementTrigger(): bool
    {
        return $this->getSettlementTriggerDate() < now();
    }
}
