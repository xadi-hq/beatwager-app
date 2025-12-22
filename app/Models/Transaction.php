<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'group_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'transactionable_type',
        'transactionable_id',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => \App\Enums\TransactionType::class,
            'amount' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'metadata' => 'array',
        ];
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
     * Get the parent transactionable model (WagerEntry, Challenge, or GroupEvent)
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the wager through the wager entry (if this is a wager transaction)
     *
     * Note: Will return null for non-wager transactions (Challenge, GroupEvent)
     */
    public function getWagerAttribute(): ?Wager
    {
        // Only fetch wager if this is a WagerEntry transaction
        if ($this->transactionable_type !== WagerEntry::class) {
            return null;
        }

        // If transactionable is already loaded, use it
        if ($this->relationLoaded('transactionable') && $this->transactionable instanceof WagerEntry) {
            $wager = $this->transactionable->wager;
            return $wager instanceof Wager ? $wager : null;
        }

        // Load via query
        $entry = WagerEntry::with('wager')->find($this->transactionable_id);
        $wager = $entry?->wager;
        return $wager instanceof Wager ? $wager : null;
    }

    /**
     * Convenience methods for backward compatibility and type safety
     */
    public function wagerEntry(): ?WagerEntry
    {
        return $this->transactionable_type === WagerEntry::class
            ? $this->transactionable
            : null;
    }

    public function challenge(): ?Challenge
    {
        return $this->transactionable_type === Challenge::class
            ? $this->transactionable
            : null;
    }

    public function event(): ?GroupEvent
    {
        return $this->transactionable_type === GroupEvent::class
            ? $this->transactionable
            : null;
    }

    /**
     * Get the wager through the entry (if this is a wager transaction)
     * Note: This is a convenience accessor, not a relationship
     */
    public function getWager(): ?Wager
    {
        $entry = $this->wagerEntry();
        return $entry?->wager;
    }
}
