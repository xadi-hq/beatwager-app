<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NudgeResponse;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperChallengeNudge extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'group_id',
        'nudged_user_id',
        'response',
        'nudged_at',
        'responded_at',
        'created_challenge_id',
    ];

    protected function casts(): array
    {
        return [
            'response' => NudgeResponse::class,
            'nudged_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function nudgedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nudged_user_id');
    }

    public function createdChallenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class, 'created_challenge_id');
    }

    /**
     * Status check methods
     */
    public function isPending(): bool
    {
        return $this->response === NudgeResponse::PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->response === NudgeResponse::ACCEPTED;
    }

    public function isDeclined(): bool
    {
        return $this->response === NudgeResponse::DECLINED;
    }

    public function isExpired(): bool
    {
        return $this->response === NudgeResponse::EXPIRED;
    }

    public function hasResponded(): bool
    {
        return $this->responded_at !== null;
    }

    public function shouldExpire(): bool
    {
        return $this->isPending()
            && $this->nudged_at->diffInHours(now()) >= 72; // 3 days
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('response', NudgeResponse::PENDING->value);
    }

    public function scopeAccepted($query)
    {
        return $query->where('response', NudgeResponse::ACCEPTED->value);
    }

    public function scopeDeclined($query)
    {
        return $query->where('response', NudgeResponse::DECLINED->value);
    }

    public function scopeExpired($query)
    {
        return $query->where('response', NudgeResponse::EXPIRED->value);
    }

    public function scopeNeedingExpiration($query)
    {
        return $query->pending()
            ->where('nudged_at', '<=', now()->subHours(72));
    }

    public function scopeForGroup($query, string $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeRecentForUser($query, string $userId, int $months = 3)
    {
        return $query->where('nudged_user_id', $userId)
            ->where('nudged_at', '>=', now()->subMonths($months));
    }
}
