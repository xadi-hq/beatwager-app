<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $user_id
 * @property string $badge_id
 * @property string|null $group_id
 * @property Carbon $awarded_at
 * @property Carbon|null $revoked_at
 * @property string|null $revocation_reason
 * @property array|null $metadata
 * @property Carbon|null $notified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $user
 * @property-read Badge $badge
 * @property-read Group|null $group
 */
class UserBadge extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'badge_id',
        'group_id',
        'awarded_at',
        'revoked_at',
        'revocation_reason',
        'metadata',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
            'revoked_at' => 'datetime',
            'notified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    // Scopes

    /**
     * Only active (non-revoked) badges.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Only revoked badges.
     */
    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Global badges only (no group association).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('group_id');
    }

    /**
     * Badges for a specific group (or global if null).
     */
    public function scopeForGroup($query, ?string $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    /**
     * Badges that haven't been notified yet.
     */
    public function scopeUnnotified($query)
    {
        return $query->whereNull('notified_at');
    }

    /**
     * Recent badges, ordered by awarded_at descending.
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('awarded_at');
    }

    // Helper methods

    /**
     * Check if this badge is currently active (not revoked).
     */
    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    /**
     * Check if this badge has been revoked.
     */
    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * Check if this is a global badge (no group).
     */
    public function isGlobal(): bool
    {
        return $this->group_id === null;
    }

    /**
     * Revoke this badge.
     */
    public function revoke(string $reason): void
    {
        $this->update([
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);
    }

    /**
     * Mark this badge as notified.
     */
    public function markNotified(): void
    {
        $this->update(['notified_at' => now()]);
    }

    /**
     * Get context from metadata (e.g., triggering wager ID).
     */
    public function getContext(string $key, $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Check if a user already has this badge in this context (active).
     *
     * This enforces the logical unique constraint: (user_id, badge_id, group_id) where revoked_at IS NULL
     * Must be called before awarding a badge to prevent duplicates.
     */
    public static function exists(string $userId, string $badgeId, ?string $groupId = null): bool
    {
        return static::where('user_id', $userId)
            ->where('badge_id', $badgeId)
            ->where('group_id', $groupId)
            ->active()
            ->exists();
    }
}
