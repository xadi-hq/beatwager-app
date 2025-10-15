<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'platform',
        'description',
        'platform_chat_id',
        'platform_chat_title',
        'platform_chat_type',
        'starting_balance',
        'point_decay_enabled',
        'point_decay_rate',
        'point_decay_grace_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'platform_chat_id' => 'string',
            'starting_balance' => 'integer',
            'point_decay_enabled' => 'boolean',
            'point_decay_rate' => 'integer',
            'point_decay_grace_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group')
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
        return $this->hasMany(Wager::class);
    }

    public function wagerTemplates(): HasMany
    {
        return $this->hasMany(WagerTemplate::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    /**
     * Send a message to this group via its platform messenger
     */
    public function sendMessage(\App\DTOs\Message $message): void
    {
        $messenger = \App\Services\MessengerFactory::for($this);
        $messenger->send($message, $this->getChatId());
    }

    /**
     * Get the platform-specific chat ID for this group
     */
    public function getChatId(): string
    {
        return $this->platform_chat_id;
    }

}
