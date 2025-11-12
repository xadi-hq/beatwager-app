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
        'type_config',
        'label_option_a',
        'label_option_b',
        'options',
        'threshold_value',
        'threshold_date',
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
        'last_settlement_reminder_sent_at',
        'settlement_reminder_count',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'type_config' => 'array',
            'betting_closes_at' => 'datetime',
            'expected_settlement_at' => 'datetime',
            'locked_at' => 'datetime',
            'settled_at' => 'datetime',
            'date_min' => 'date',
            'date_max' => 'date',
            'threshold_date' => 'date',
            'threshold_value' => 'decimal:2',
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
            'binary' => [$this->label_option_a ?? 'Yes', $this->label_option_b ?? 'No'],
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

    /**
     * Check if wager should be deleted (expired with no participants)
     */
    public function shouldBeDeleted(): bool
    {
        return $this->status === 'open'
            && $this->isPastBettingDeadline()
            && $this->participants_count === 0;
    }

    /**
     * Check if wager has auto-settlement capability (threshold-based)
     */
    public function hasAutoSettlement(): bool
    {
        return $this->threshold_value !== null || $this->threshold_date !== null;
    }

    /**
     * Determine outcome based on threshold comparison
     * Returns 'yes' if condition met, 'no' otherwise
     *
     * @param mixed $actualValue The actual value to compare (number or date string)
     * @return string 'yes' or 'no'
     */
    public function determineThresholdOutcome($actualValue): string
    {
        if ($this->threshold_value !== null) {
            // Over/Under logic: >= threshold counts as "over" (option_a/yes)
            $actual = is_numeric($actualValue) ? (float) $actualValue : 0;
            return $actual >= (float) $this->threshold_value ? 'yes' : 'no';
        }

        if ($this->threshold_date !== null) {
            // Before/After logic: < threshold counts as "before" (option_a/yes)
            $actual = \Carbon\Carbon::parse($actualValue);
            $threshold = \Carbon\Carbon::parse($this->threshold_date);
            return $actual->lt($threshold) ? 'yes' : 'no';
        }

        throw new \LogicException('Cannot determine threshold outcome without threshold_value or threshold_date');
    }

    /**
     * Check if wager requires landing page for input (complex types)
     */
    public function requiresLandingPage(): bool
    {
        return in_array($this->type, [
            'short_answer',
            'top_n_ranking',
            // Future: 'numeric', 'date' for enhanced UX
        ]);
    }

    /**
     * Get unified type configuration (column-based or JSON)
     */
    public function getTypeConfig(): array
    {
        return match($this->type) {
            'binary' => [
                'label_option_a' => $this->label_option_a,
                'label_option_b' => $this->label_option_b,
                'threshold_value' => $this->threshold_value,
                'threshold_date' => $this->threshold_date,
            ],
            'multiple_choice' => [
                'options' => $this->options,
            ],
            'numeric' => [
                'min' => $this->numeric_min,
                'max' => $this->numeric_max,
                'winner_type' => $this->numeric_winner_type,
            ],
            'date' => [
                'min' => $this->date_min,
                'max' => $this->date_max,
                'winner_type' => $this->date_winner_type,
            ],
            default => $this->type_config ?? []
        };
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('status', '!=', 'open')
              ->orWhere('betting_closes_at', '>=', now())
              ->orWhere('participants_count', '>', 0);
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'open')
                    ->where('betting_closes_at', '<', now())
                    ->where('participants_count', 0);
    }
}
