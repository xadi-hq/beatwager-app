<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class WagerEntry extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'wager_id',
        'user_id',
        'group_id',
        'answer_value',
        'numeric_distance',
        'date_distance_days',
        'points_wagered',
        'result',
        'is_winner',
        'points_won',
        'points_lost',
    ];

    protected function casts(): array
    {
        return [
            'points_wagered' => 'integer',
            'numeric_distance' => 'integer',
            'date_distance_days' => 'integer',
            'points_won' => 'integer',
            'points_lost' => 'integer',
        ];
    }

    public function wager(): BelongsTo
    {
        return $this->belongsTo(Wager::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get all transactions for this wager entry
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    /**
     * Get the formatted answer based on wager type
     */
    public function getFormattedAnswer(): string
    {
        $wager = $this->wager;

        return match ($wager->type) {
            'binary' => $this->answer_value === 'yes' ? 'Yes' : 'No',
            'multiple_choice' => $this->answer_value,
            'numeric' => $this->answer_value,
            'date' => $this->answer_value,
            default => $this->answer_value,
        };
    }
}
