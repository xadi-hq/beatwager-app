<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Enums\DisputeVoteOutcome;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $group_id
 * @property string $disputable_type
 * @property string $disputable_id
 * @property string $reporter_id
 * @property string $accused_id
 * @property bool $is_self_report
 * @property DisputeStatus $status
 * @property DisputeResolution|null $resolution
 * @property string $original_outcome
 * @property string|null $corrected_outcome
 * @property int $votes_required
 * @property Carbon|null $resolved_at
 * @property Carbon $expires_at
 * @property Carbon|null $reminder_sent_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Group $group
 * @property-read Model $disputable
 * @property-read User $reporter
 * @property-read User $accused
 * @property-read Collection<int, DisputeVote> $votes
 */
class Dispute extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'group_id',
        'disputable_type',
        'disputable_id',
        'reporter_id',
        'accused_id',
        'is_self_report',
        'status',
        'resolution',
        'original_outcome',
        'corrected_outcome',
        'votes_required',
        'resolved_at',
        'expires_at',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'is_self_report' => 'boolean',
            'status' => DisputeStatus::class,
            'resolution' => DisputeResolution::class,
            'resolved_at' => 'datetime',
            'expires_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'votes_required' => 'integer',
        ];
    }

    // Relationships

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function disputable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function accused(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accused_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(DisputeVote::class);
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', DisputeStatus::Pending);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', DisputeStatus::Resolved);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', DisputeStatus::Pending)
                     ->where('expires_at', '<', now());
    }

    public function scopeActive($query)
    {
        return $query->where('status', DisputeStatus::Pending)
                     ->where('expires_at', '>=', now());
    }

    // Helper methods

    public function isPending(): bool
    {
        return $this->status === DisputeStatus::Pending;
    }

    public function isResolved(): bool
    {
        return $this->status === DisputeStatus::Resolved;
    }

    public function isExpired(): bool
    {
        return $this->isPending() && $this->expires_at->isPast();
    }

    public function hasEnoughVotes(): bool
    {
        return $this->votes()->count() >= $this->votes_required;
    }

    public function getVoteCount(): int
    {
        return $this->votes()->count();
    }

    public function getRemainingVotesNeeded(): int
    {
        return max(0, $this->votes_required - $this->getVoteCount());
    }

    public function getTimeRemaining(): ?string
    {
        if ($this->isResolved() || $this->isExpired()) {
            return null;
        }

        return $this->expires_at->diffForHumans();
    }

    /**
     * Check if a user can vote on this dispute.
     * Must be a group member, not the reporter, and not the accused.
     */
    public function canUserVote(User $user): bool
    {
        // Must be pending
        if (!$this->isPending()) {
            return false;
        }

        // Must not be expired
        if ($this->isExpired()) {
            return false;
        }

        // Must not be reporter or accused
        if ($user->id === $this->reporter_id || $user->id === $this->accused_id) {
            return false;
        }

        // Must be a member of the group
        if (!$this->group->users()->where('users.id', $user->id)->exists()) {
            return false;
        }

        // Must not have already voted
        if ($this->votes()->where('voter_id', $user->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get eligible voters for this dispute.
     */
    public function getEligibleVoters(): Collection
    {
        return $this->group->users()
            ->where('users.id', '!=', $this->reporter_id)
            ->where('users.id', '!=', $this->accused_id)
            ->whereNotIn('users.id', $this->votes()->pluck('voter_id'))
            ->get();
    }

    /**
     * Get vote tally grouped by outcome.
     */
    public function getVoteTally(): array
    {
        $tally = [
            DisputeVoteOutcome::OriginalCorrect->value => 0,
            DisputeVoteOutcome::DifferentOutcome->value => 0,
            DisputeVoteOutcome::NotYetDeterminable->value => 0,
        ];

        foreach ($this->votes as $vote) {
            $tally[$vote->vote_outcome->value]++;
        }

        return $tally;
    }

    /**
     * Determine the winning outcome based on votes.
     * Returns null if no clear majority or not enough votes.
     */
    public function determineWinningOutcome(): ?DisputeVoteOutcome
    {
        if (!$this->hasEnoughVotes()) {
            return null;
        }

        $tally = $this->getVoteTally();
        $maxVotes = max($tally);
        $winners = array_keys(array_filter($tally, fn($count) => $count === $maxVotes));

        // If there's a tie, return null (needs more votes or manual resolution)
        if (count($winners) > 1) {
            return null;
        }

        return DisputeVoteOutcome::from($winners[0]);
    }

    /**
     * Get the corrected outcome from votes (most common selected_outcome).
     */
    public function getCorrectedOutcomeFromVotes(): ?string
    {
        $differentOutcomeVotes = $this->votes()
            ->where('vote_outcome', DisputeVoteOutcome::DifferentOutcome)
            ->whereNotNull('selected_outcome')
            ->pluck('selected_outcome');

        if ($differentOutcomeVotes->isEmpty()) {
            return null;
        }

        // Return the most common outcome
        return $differentOutcomeVotes
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();
    }

    /**
     * Calculate required votes based on group size.
     * Group ≤3: 1 vote, Group >3: 2 votes
     */
    public static function calculateVotesRequired(Group $group, User $reporter, User $accused): int
    {
        // Count eligible voters (exclude reporter and accused)
        $eligibleCount = $group->users()
            ->where('users.id', '!=', $reporter->id)
            ->where('users.id', '!=', $accused->id)
            ->count();

        // If group size ≤3 (meaning ≤1 eligible voter), require 1 vote
        // If group size >3 (meaning ≥2 eligible voters), require 2 votes
        return $eligibleCount <= 1 ? 1 : 2;
    }
}
