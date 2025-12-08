<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ValidationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $challenge_id
 * @property string $user_id
 * @property Carbon $accepted_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $eliminated_at
 * @property string|null $elimination_note
 * @property ValidationStatus $validation_status
 * @property Carbon|null $validated_by_creator_at
 * @property Carbon|null $auto_validated_at
 * @property string|null $prize_transaction_id
 * @property string|null $buy_in_transaction_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Challenge $challenge
 * @property-read User|null $user
 * @property-read Transaction|null $prizeTransaction
 * @property-read Transaction|null $buyInTransaction
 */
class ChallengeParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'challenge_id',
        'user_id',
        'accepted_at',
        'completed_at',
        'eliminated_at',
        'elimination_note',
        'validation_status',
        'validated_by_creator_at',
        'auto_validated_at',
        'prize_transaction_id',
        'buy_in_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'validation_status' => ValidationStatus::class,
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
            'eliminated_at' => 'datetime',
            'validated_by_creator_at' => 'datetime',
            'auto_validated_at' => 'datetime',
        ];
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prizeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'prize_transaction_id');
    }

    public function buyInTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'buy_in_transaction_id');
    }

    /**
     * Status check methods
     */
    public function isPending(): bool
    {
        return $this->validation_status === ValidationStatus::PENDING;
    }

    public function isValidated(): bool
    {
        return $this->validation_status === ValidationStatus::VALIDATED;
    }

    public function isRejected(): bool
    {
        return $this->validation_status === ValidationStatus::REJECTED;
    }

    public function hasCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isAwaitingValidation(): bool
    {
        return $this->hasCompleted() && $this->isPending();
    }

    public function wasAutoValidated(): bool
    {
        return $this->auto_validated_at !== null;
    }

    public function needsAutoValidation(): bool
    {
        return $this->isPending()
            && $this->completed_at !== null
            && $this->completed_at->diffInHours(now()) >= 48;
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('validation_status', ValidationStatus::PENDING->value);
    }

    public function scopeValidated($query)
    {
        return $query->where('validation_status', ValidationStatus::VALIDATED->value);
    }

    public function scopeRejected($query)
    {
        return $query->where('validation_status', ValidationStatus::REJECTED->value);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeAwaitingValidation($query)
    {
        return $query->whereNotNull('completed_at')
            ->where('validation_status', 'pending');
    }

    public function scopeNeedingAutoValidation($query)
    {
        return $query->pending()
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', now()->subHours(48));
    }

    /**
     * Elimination Challenge methods
     */

    public function isEliminated(): bool
    {
        return $this->eliminated_at !== null;
    }

    public function isSurvivor(): bool
    {
        return $this->eliminated_at === null;
    }

    public function getDaysSurvived(): int
    {
        $endDate = $this->eliminated_at ?? now();
        return (int) $this->accepted_at->diffInDays($endDate);
    }

    /**
     * Scopes for Elimination Challenges
     */
    public function scopeSurvivors($query)
    {
        return $query->whereNull('eliminated_at');
    }

    public function scopeEliminated($query)
    {
        return $query->whereNotNull('eliminated_at');
    }
}
