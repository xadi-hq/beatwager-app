<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LlmUsageDaily extends Model
{
    use HasUuids;

    protected $table = 'llm_usage_daily';

    protected $fillable = [
        'group_id',
        'date',
        'total_calls',
        'cached_calls',
        'fallback_calls',
        'estimated_cost_usd',
        'providers_breakdown',
        'message_types',
    ];

    protected $casts = [
        'date' => 'date',
        'estimated_cost_usd' => 'decimal:4',
        'providers_breakdown' => 'array',
        'message_types' => 'array',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
