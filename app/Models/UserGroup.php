<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserGroup extends Pivot
{
    use HasUuids;

    protected $table = 'user_group';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'group_id',
        'points',
        'points_earned',
        'points_spent',
        'last_wager_joined_at',
        'last_activity_at',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'points_earned' => 'integer',
            'points_spent' => 'integer',
            'last_wager_joined_at' => 'datetime',
            'last_activity_at' => 'datetime',
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
}
