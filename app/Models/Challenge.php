<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'group_id',
        'creator_id',
        'acceptor_id',
        'description',
        'amount',
        'acceptance_deadline',
        'completion_deadline',
        'status',
        'accepted_at',
        'submitted_at',
        'verified_at',
        'completed_at',
        'failed_at',
        'cancelled_at',
        'verified_by_id',
        'cancelled_by_id',
        'failure_reason',
        'submission_note',
        'submission_media',
        'hold_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'acceptance_deadline' => 'datetime',
            'completion_deadline' => 'datetime',
            'accepted_at' => 'datetime',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'submission_media' => 'array',
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

    public function acceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acceptor_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_id');
    }

    public function holdTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'hold_transaction_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Status check methods
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Deadline check methods
     */
    public function isAcceptanceExpired(): bool
    {
        return $this->acceptance_deadline !== null && $this->acceptance_deadline->isPast();
    }

    public function isPastCompletionDeadline(): bool
    {
        return $this->completion_deadline->isPast();
    }

    public function canBeAccepted(): bool
    {
        return $this->isOpen() && !$this->isAcceptanceExpired();
    }

    public function canBeSubmitted(): bool
    {
        return $this->isAccepted() && !$this->isPastCompletionDeadline();
    }

    public function isAwaitingReview(): bool
    {
        return $this->isAccepted() && $this->submitted_at !== null && $this->verified_at === null;
    }

    public function needsExpiration(): bool
    {
        return $this->isOpen() && $this->isAcceptanceExpired();
    }

    public function needsDeadlineFailure(): bool
    {
        return $this->isAccepted() && $this->isPastCompletionDeadline() && $this->verified_at === null;
    }

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('creator_id', $userId)
              ->orWhere('acceptor_id', $userId);
        });
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'accepted')
                    ->whereNotNull('submitted_at')
                    ->whereNull('verified_at');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'accepted']);
    }
}
