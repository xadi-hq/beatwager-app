<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DisputeVoteOutcome;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $dispute_id
 * @property string $voter_id
 * @property DisputeVoteOutcome $vote_outcome
 * @property string|null $selected_outcome
 * @property Carbon $created_at
 *
 * @property-read Dispute $dispute
 * @property-read User $voter
 */
class DisputeVote extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'dispute_id',
        'voter_id',
        'vote_outcome',
        'selected_outcome',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'vote_outcome' => DisputeVoteOutcome::class,
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DisputeVote $vote) {
            $vote->created_at = $vote->created_at ?? now();
        });
    }

    // Relationships

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    // Helper methods

    public function isOriginalCorrect(): bool
    {
        return $this->vote_outcome === DisputeVoteOutcome::OriginalCorrect;
    }

    public function isDifferentOutcome(): bool
    {
        return $this->vote_outcome === DisputeVoteOutcome::DifferentOutcome;
    }

    public function isNotYetDeterminable(): bool
    {
        return $this->vote_outcome === DisputeVoteOutcome::NotYetDeterminable;
    }
}
