<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'wager_id',
        'wager_entry_id',
        'challenge_id',
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

    public function wager(): BelongsTo
    {
        return $this->belongsTo(Wager::class);
    }

    public function wagerEntry(): BelongsTo
    {
        return $this->belongsTo(WagerEntry::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }
}
