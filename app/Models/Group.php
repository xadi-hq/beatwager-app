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
        'timezone',
        'language',
        'point_decay_enabled',
        'point_decay_rate',
        'point_decay_grace_days',
        'is_active',
        'last_activity_at',
        'inactivity_threshold_days',
        'points_currency_name',
        'notification_preferences',
        'llm_api_key',
        'llm_provider',
        'llm_model',
        'bot_tone',
        'allow_nsfw',
        'group_type',
        'settings',
        'current_season_id',
        'season_ends_at',
        'surprise_drops_enabled',
        'season_milestones_triggered',
        'superchallenge_frequency',
        'last_superchallenge_at',
        'house_pot',
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
            'last_activity_at' => 'datetime',
            'inactivity_threshold_days' => 'integer',
            'allow_nsfw' => 'boolean',
            'notification_preferences' => 'array',
            'llm_api_key' => 'encrypted',
            'settings' => 'array',
            'season_ends_at' => 'datetime',
            'surprise_drops_enabled' => 'boolean',
            'season_milestones_triggered' => 'array',
            'last_superchallenge_at' => 'datetime',
            'house_pot' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
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

    public function events(): HasMany
    {
        return $this->hasMany(GroupEvent::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(GroupSeason::class)->orderBy('season_number', 'desc');
    }

    public function currentSeason(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupSeason::class, 'current_season_id');
    }

    public function scheduledMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ScheduledMessage::class);
    }

    /**
     * Send a message to this group via its platform messenger
     *
     * Note: LLM enhancement already handled in MessageService via ContentGenerator
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

    /**
     * Convert a UTC datetime to the group's timezone
     */
    public function toGroupTimezone(\DateTime|\DateTimeImmutable|string $datetime): \Carbon\Carbon
    {
        $carbon = $datetime instanceof \Carbon\Carbon
            ? $datetime
            : \Carbon\Carbon::parse($datetime);

        return $carbon->copy()->setTimezone($this->timezone ?? 'UTC');
    }

    /**
     * Convert a datetime from the group's timezone to UTC
     */
    public function toUtc(\DateTime|\DateTimeImmutable|string $datetime): \Carbon\Carbon
    {
        // Parse the datetime string assuming it's in the group's timezone
        $carbon = \Carbon\Carbon::parse($datetime, $this->timezone ?? 'UTC');

        // Convert to UTC
        return $carbon->setTimezone('UTC');
    }

}
