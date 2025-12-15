<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BadgeCategory;
use App\Enums\BadgeCriteriaType;
use App\Enums\BadgeTier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $description
 * @property BadgeCategory $category
 * @property BadgeTier $tier
 * @property bool $is_shame
 * @property BadgeCriteriaType $criteria_type
 * @property string $criteria_event
 * @property int|null $criteria_threshold
 * @property array|null $criteria_config
 * @property string $image_slug
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Collection<int, UserBadge> $userBadges
 */
class Badge extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'category',
        'tier',
        'is_shame',
        'criteria_type',
        'criteria_event',
        'criteria_threshold',
        'criteria_config',
        'image_slug',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => BadgeCategory::class,
            'tier' => BadgeTier::class,
            'criteria_type' => BadgeCriteriaType::class,
            'criteria_threshold' => 'integer',
            'criteria_config' => 'array',
            'sort_order' => 'integer',
            'is_shame' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Get all users who have earned this badge (active only).
     *
     * Note: If a user earned this badge in multiple groups, they will appear
     * multiple times. Use ->distinct() if you need unique users only.
     */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            UserBadge::class,
            'badge_id',     // Foreign key on user_badges table
            'id',           // Foreign key on users table
            'id',           // Local key on badges table
            'user_id'       // Local key on user_badges table
        )->whereNull('user_badges.revoked_at');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, BadgeCategory $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByTier($query, BadgeTier $tier)
    {
        return $query->where('tier', $tier);
    }

    public function scopeByEvent($query, string $criteriaEvent)
    {
        return $query->where('criteria_event', $criteriaEvent);
    }

    public function scopeShame($query)
    {
        return $query->where('is_shame', true);
    }

    public function scopePositive($query)
    {
        return $query->where('is_shame', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('category')
                     ->orderBy('sort_order')
                     ->orderBy('tier');
    }

    // Accessors

    /**
     * Get the web-accessible URL for this badge image.
     * Used for displaying in web UI and sending via messaging platforms.
     */
    public function getImageUrlAttribute(): string
    {
        return $this->getImageUrl();
    }

    /**
     * Get the web-accessible URL for the badge image.
     *
     * @param string $size 'default', 'large', or 'small'
     */
    public function getImageUrl(string $size = 'default'): string
    {
        $filename = match ($size) {
            'large' => "{$this->image_slug}-large.png",
            'small' => "{$this->image_slug}-small.png",
            default => "{$this->image_slug}.png",
        };

        // Use S3 storage if configured, otherwise fall back to local public storage
        $disk = config('badges.storage_disk', 'public');
        $path = config('badges.storage_path', 'badges');

        if ($disk === 's3') {
            return Storage::disk('s3')->url("{$path}/{$filename}");
        }

        // Fall back to local public storage
        return url("storage/{$path}/{$filename}");
    }

    /**
     * Get the full filesystem path for the badge image.
     * Used for file existence checks and local file operations.
     *
     * @param string $size 'default', 'large', or 'small'
     */
    public function getStoragePath(string $size = 'default'): string
    {
        $filename = match ($size) {
            'large' => "{$this->image_slug}-large.png",
            'small' => "{$this->image_slug}-small.png",
            default => "{$this->image_slug}.png",
        };

        return storage_path("badges/{$filename}");
    }

    /**
     * Check if the badge image exists on disk.
     */
    public function hasImage(): bool
    {
        return file_exists($this->getStoragePath());
    }

    // Helper methods

    /**
     * Get the border color for this badge's tier.
     */
    public function getBorderColor(): string
    {
        return $this->tier->color();
    }

    /**
     * Get display label combining name and tier.
     */
    public function getDisplayLabel(): string
    {
        if ($this->tier === BadgeTier::Standard) {
            return $this->name;
        }

        return "{$this->name} ({$this->tier->label()})";
    }

    /**
     * Check if this badge requires a threshold check.
     */
    public function requiresThreshold(): bool
    {
        return in_array($this->criteria_type, [
            BadgeCriteriaType::Count,
            BadgeCriteriaType::Streak,
        ]);
    }

    /**
     * Check if this badge can be revoked.
     */
    public function isRevocable(): bool
    {
        return $this->criteria_type->isRevocable();
    }

    /**
     * Get all badges that should be checked for a specific event.
     */
    public static function forEvent(string $criteriaEvent): Collection
    {
        return static::active()
            ->byEvent($criteriaEvent)
            ->ordered()
            ->get();
    }

    /**
     * Get all badges organized by category.
     */
    public static function byCategories(): array
    {
        $badges = static::active()->ordered()->get();

        $categorized = [];
        foreach (BadgeCategory::cases() as $category) {
            $categorized[$category->value] = $badges->filter(
                fn($badge) => $badge->category === $category
            )->values();
        }

        return $categorized;
    }
}
