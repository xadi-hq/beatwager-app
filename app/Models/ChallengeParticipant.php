<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ValidationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'challenge_id',
        'user_id',
        'accepted_at',
        'completed_at',
        'validation_status',
        'validated_by_creator_at',
        'auto_validated_at',
        'prize_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'validation_status' => ValidationStatus::class,
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
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
}
