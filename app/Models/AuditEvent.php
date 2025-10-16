<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit events are human-readable summaries of important group activities
 * Used for LLM context building and narrative history
 */
class AuditEvent extends Model
{
    use HasUuids;

    public $timestamps = false; // Only created_at

    protected $fillable = [
        'group_id',
        'event_type',
        'summary',
        'participants',
        'impact',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'participants' => 'array',
            'impact' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the group this event belongs to
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get recent events for a group (for LLM context)
     */
    public static function recentForGroup(string $groupId, int $limit = 20): \Illuminate\Support\Collection
    {
        return self::where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope to filter by event type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope to filter events involving a specific user
     */
    public function scopeInvolvingUser($query, string $userId)
    {
        return $query->whereJsonContains('participants', ['user_id' => $userId]);
    }

    /**
     * Scope for recent events (within hours)
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>', now()->subHours($hours));
    }
}
