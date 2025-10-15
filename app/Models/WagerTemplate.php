<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WagerTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'group_id',
        'creator_id',
        'title',
        'description',
        'resolution_criteria',
        'option_yes',
        'option_no',
        'usage_count',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
            'last_used_at' => 'datetime',
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
}
