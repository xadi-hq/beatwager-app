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
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
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
}
