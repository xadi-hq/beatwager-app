<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasUuids;

    public $timestamps = false; // Only created_at, no updated_at

    protected $fillable = [
        'actor_id',
        'actor_type',
        'action',
        'auditable_type',
        'auditable_id',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the actor (user who performed the action)
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the auditable entity (polymorphic)
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for filtering by action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by actor
     */
    public function scopeByActor($query, $actorId)
    {
        return $query->where('actor_id', $actorId);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>', now()->subHours($hours));
    }
}
