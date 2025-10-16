<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'taunt_line',
        'birthday',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
        ];
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->using(UserGroup::class)
            ->withPivot([
                'points',
                'points_earned',
                'points_spent',
                'last_wager_joined_at',
                'last_activity_at',
                'role',
            ])
            ->withTimestamps();
    }

    public function wagers(): HasMany
    {
        return $this->hasMany(Wager::class, 'creator_id');
    }

    public function wagerEntries(): HasMany
    {
        return $this->hasMany(WagerEntry::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function messengerServices(): HasMany
    {
        return $this->hasMany(MessengerService::class);
    }

    // Helper to get specific platform service
    public function getMessengerService(string $platform): ?MessengerService
    {
        return $this->messengerServices()->where('platform', $platform)->first();
    }

    // Helper to get Telegram service (most common)
    public function getTelegramService(): ?MessengerService
    {
        return $this->getMessengerService('telegram');
    }
}
