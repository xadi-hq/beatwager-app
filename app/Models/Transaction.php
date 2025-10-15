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
     * This is a proper Eloquent relationship that can be eager loaded
     *
     * Note: Will return null for non-wager transactions (Challenge, GroupEvent)
     */
    public function wager(): HasOneThrough
    {
        return $this->hasOneThrough(
            Wager::class,
            WagerEntry::class,
            'id', // Foreign key on wager_entries table
            'id', // Foreign key on wagers table
            'transactionable_id', // Local key on transactions table
            'wager_id' // Local key on wager_entries table
        )->select('wagers.*', 'wager_entries.id as laravel_through_key');
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
