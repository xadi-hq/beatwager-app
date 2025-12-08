<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChallengeType;
use App\Enums\EliminationMode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property string $id
 * @property string $group_id
 * @property string|null $creator_id
 * @property string|null $acceptor_id
 * @property ChallengeType $type
 * @property string $description
 * @property int|null $amount
 * @property int|null $prize_per_person
 * @property int|null $max_participants
 * @property string|null $evidence_guidance
 * @property string|null $elimination_trigger
 * @property EliminationMode|null $elimination_mode
 * @property int|null $point_pot
 * @property int|null $buy_in_amount
 * @property int $min_participants
 * @property Carbon|null $tap_in_deadline
 * @property Carbon|null $acceptance_deadline
 * @property Carbon|null $completion_deadline
 * @property string $status
 * @property Carbon|null $accepted_at
 * @property Carbon|null $submitted_at
 * @property Carbon|null $verified_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $failed_at
 * @property Carbon|null $cancelled_at
 * @property string|null $verified_by_id
 * @property string|null $cancelled_by_id
 * @property string|null $failure_reason
 * @property string|null $submission_note
 * @property array<string>|null $submission_media
 * @property string|null $hold_transaction_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Group|null $group
 * @property-read User|null $creator
 * @property-read User|null $acceptor
 * @property-read User|null $verifiedBy
 * @property-read User|null $cancelledBy
 * @property-read Transaction|null $holdTransaction
 * @property-read Collection<int, ChallengeParticipant> $participants
 * @property-read Collection<int, Transaction> $transactions
 */
class Challenge extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'group_id',
        'creator_id',
        'acceptor_id',
        'type',
        'description',
        'amount',
        'prize_per_person',
        'max_participants',
        'evidence_guidance',
        'elimination_trigger',
        'elimination_mode',
        'point_pot',
        'last_countdown_hours',
        'milestones_sent',
        'buy_in_amount',
        'tap_in_deadline',
        'min_participants',
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
            'type' => ChallengeType::class,
            'elimination_mode' => EliminationMode::class,
            'amount' => 'integer',
            'prize_per_person' => 'integer',
            'max_participants' => 'integer',
            'point_pot' => 'integer',
            'last_countdown_hours' => 'integer',
            'milestones_sent' => 'array',
            'buy_in_amount' => 'integer',
            'min_participants' => 'integer',
            'acceptance_deadline' => 'datetime',
            'completion_deadline' => 'datetime',
            'tap_in_deadline' => 'datetime',
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
        return $query->where(function ($q) {
            // Accepted or completed challenges are always active
            $q->whereIn('status', ['accepted', 'completed'])
              // Open 1-on-1 challenges with valid acceptance deadline
              ->orWhere(function ($subQ) {
                  $subQ->where('status', 'open')
                       ->where('type', ChallengeType::USER_CHALLENGE->value)
                       ->where(function ($deadlineQ) {
                           $deadlineQ->where('acceptance_deadline', '>=', now())
                                    ->orWhereNotNull('acceptor_id');
                       });
              })
              // Open SuperChallenges that haven't reached completion deadline
              ->orWhere(function ($superQ) {
                  $superQ->where('status', 'open')
                         ->where('type', ChallengeType::SUPER_CHALLENGE->value)
                         ->where('completion_deadline', '>=', now());
              });
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'open')
                    ->where('acceptance_deadline', '<', now())
                    ->whereNull('acceptor_id');
    }

    /**
     * Check if challenge should be deleted (expired with no acceptor)
     */
    public function shouldBeDeleted(): bool
    {
        return $this->status === 'open'
            && $this->isAcceptanceExpired()
            && $this->acceptor_id === null;
    }

    /**
     * Challenge Type Methods
     */

    /**
     * Check if this is an "offering payment" challenge (Type 1)
     * Creator pays points, acceptor receives points
     * Example: "I'll pay 200 points for someone to clean my car"
     */
    public function isOfferingPayment(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if this is an "offering service" challenge (Type 2)
     * Acceptor pays points, creator receives points
     * Example: "I'll clean your car for 200 points"
     */
    public function isOfferingService(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Get absolute amount value for display
     */
    public function getAbsoluteAmount(): int
    {
        return abs($this->amount);
    }

    /**
     * Get who pays for this challenge
     */
    public function getPayer(): User
    {
        return $this->isOfferingPayment() ? $this->creator : $this->acceptor;
    }

    /**
     * Get who receives payment for this challenge
     */
    public function getPayee(): User
    {
        return $this->isOfferingPayment() ? $this->acceptor : $this->creator;
    }

    /**
     * Get all transactions for this challenge
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    /**
     * SuperChallenge relationships and methods
     */

    public function participants(): HasMany
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    public function isSuperChallenge(): bool
    {
        return $this->type === ChallengeType::SUPER_CHALLENGE;
    }

    public function isUserChallenge(): bool
    {
        return $this->type === ChallengeType::USER_CHALLENGE;
    }

    public function hasReachedMaxParticipants(): bool
    {
        if (!$this->isSuperChallenge() || $this->max_participants === null) {
            return false;
        }

        return $this->participants()->count() >= $this->max_participants;
    }

    public function getValidatedParticipantsCount(): int
    {
        return $this->participants()
            ->where('validation_status', 'validated')
            ->count();
    }

    public function getTotalPrizeMinted(): int
    {
        return $this->getValidatedParticipantsCount() * ($this->prize_per_person ?? 0);
    }

    /**
     * Scopes for SuperChallenges
     */
    public function scopeSuperChallenges($query)
    {
        return $query->where('type', ChallengeType::SUPER_CHALLENGE->value);
    }

    public function scopeUserChallenges($query)
    {
        return $query->where('type', ChallengeType::USER_CHALLENGE->value);
    }

    public function scopeActiveSuperChallenges($query)
    {
        return $query->superChallenges()
            ->where('status', 'open')
            ->where('completion_deadline', '>=', now());
    }

    /**
     * Elimination Challenge methods
     */

    public function isEliminationChallenge(): bool
    {
        return $this->type === ChallengeType::ELIMINATION_CHALLENGE;
    }

    public function isLastManStanding(): bool
    {
        return $this->elimination_mode === EliminationMode::LAST_MAN_STANDING;
    }

    public function isDeadlineMode(): bool
    {
        return $this->elimination_mode === EliminationMode::DEADLINE;
    }

    public function isTapInOpen(): bool
    {
        if (!$this->isEliminationChallenge() || !$this->isOpen()) {
            return false;
        }

        if ($this->tap_in_deadline === null) {
            return true;
        }

        return $this->tap_in_deadline->isFuture();
    }

    public function isTapInClosed(): bool
    {
        return $this->tap_in_deadline !== null && $this->tap_in_deadline->isPast();
    }

    public function hasMinimumParticipants(): bool
    {
        return $this->participants()->count() >= $this->min_participants;
    }

    public function getSurvivors()
    {
        return $this->participants()
            ->whereNull('eliminated_at')
            ->get();
    }

    public function getSurvivorCount(): int
    {
        return $this->participants()
            ->whereNull('eliminated_at')
            ->count();
    }

    public function getEliminatedCount(): int
    {
        return $this->participants()
            ->whereNotNull('eliminated_at')
            ->count();
    }

    public function getPotPerSurvivor(): int
    {
        $survivors = $this->getSurvivorCount();
        if ($survivors === 0) {
            return 0;
        }

        return (int) floor($this->point_pot / $survivors);
    }

    public function getEliminationPercentage(): float
    {
        $total = $this->participants()->count();
        if ($total === 0) {
            return 0.0;
        }

        return ($this->getEliminatedCount() / $total) * 100;
    }

    public function shouldResolve(): bool
    {
        if (!$this->isEliminationChallenge() || !$this->isOpen()) {
            return false;
        }

        // Last man standing: resolve when 1 survivor
        if ($this->isLastManStanding() && $this->getSurvivorCount() === 1) {
            return true;
        }

        // Deadline mode: resolve when deadline passed
        if ($this->isDeadlineMode() && $this->completion_deadline?->isPast()) {
            return true;
        }

        return false;
    }

    public function shouldAutoCancel(): bool
    {
        if (!$this->isEliminationChallenge() || !$this->isOpen()) {
            return false;
        }

        // Auto-cancel if tap-in closed and not enough participants
        return $this->isTapInClosed() && !$this->hasMinimumParticipants();
    }

    /**
     * Scopes for Elimination Challenges
     *
     * @param \Illuminate\Database\Eloquent\Builder<Challenge> $query
     * @return \Illuminate\Database\Eloquent\Builder<Challenge>
     */
    public function scopeEliminationChallenges(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type', ChallengeType::ELIMINATION_CHALLENGE->value);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<Challenge> $query
     * @return \Illuminate\Database\Eloquent\Builder<Challenge>
     */
    public function scopeActiveEliminationChallenges(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->eliminationChallenges()
            ->where('status', 'open')
            ->where(function ($q) {
                $q->whereNull('completion_deadline')
                  ->orWhere('completion_deadline', '>=', now());
            });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<Challenge> $query
     * @return \Illuminate\Database\Eloquent\Builder<Challenge>
     */
    public function scopeNeedingResolution(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->eliminationChallenges()
            ->where('status', 'open')
            ->where('completion_deadline', '<', now());
    }

    public function scopeNeedingAutoCancel($query)
    {
        return $query->eliminationChallenges()
            ->where('status', 'open')
            ->whereNotNull('tap_in_deadline')
            ->where('tap_in_deadline', '<', now())
            ->whereRaw('(SELECT COUNT(*) FROM challenge_participants WHERE challenge_id = challenges.id) < min_participants');
    }
}
