<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessengerService extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'platform_user_id',
        'username',
        'first_name',
        'last_name',
        'photo_url',
        'metadata',
        'is_primary',
        'fraud_offense_count',
        'last_fraud_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_primary' => 'boolean',
            'fraud_offense_count' => 'integer',
            'last_fraud_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getDisplayNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }

        if ($this->first_name) {
            return $this->first_name;
        }

        if ($this->username) {
            return '@' . $this->username;
        }

        return $this->platform_user_id;
    }

    // Platform-specific scopes
    public function scopeTelegram($query)
    {
        return $query->where('platform', 'telegram');
    }

    public function scopeDiscord($query)
    {
        return $query->where('platform', 'discord');
    }

    public function scopeSlack($query)
    {
        return $query->where('platform', 'slack');
    }

    // Find by platform and platform_user_id
    public static function findByPlatform(string $platform, string $platformUserId): ?self
    {
        return self::where('platform', $platform)
            ->where('platform_user_id', $platformUserId)
            ->first();
    }

    // Fraud tracking methods

    /**
     * Increment the fraud offense count and record the timestamp.
     */
    public function incrementFraudCount(): void
    {
        $this->increment('fraud_offense_count');
        $this->update(['last_fraud_at' => now()]);
    }

    /**
     * Get the penalty percentage based on fraud offense count.
     * First offense: 25%, subsequent: 50%
     */
    public function getFraudPenaltyPercentage(): int
    {
        return $this->fraud_offense_count > 0 ? 50 : 25;
    }

    /**
     * Check if this is a repeat offender.
     */
    public function isRepeatOffender(): bool
    {
        return $this->fraud_offense_count > 0;
    }

    /**
     * Get the fraud offense count.
     */
    public function getFraudOffenseCount(): int
    {
        return $this->fraud_offense_count ?? 0;
    }
}
