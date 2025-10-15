<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WagerSettlementToken extends Model
{
    use HasUuids;

    protected $fillable = [
        'token',
        'wager_id',
        'creator_id',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function wager(): BelongsTo
    {
        return $this->belongsTo(Wager::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Generate a unique settlement token for a wager
     */
    public static function generate(
        Wager $wager,
        int $expiresInHours = 24
    ): self {
        do {
            $token = Str::random(32);
        } while (self::where('token', $token)->exists());

        return self::create([
            'token' => $token,
            'wager_id' => $wager->id,
            'creator_id' => $wager->creator_id,
            'expires_at' => now()->addHours($expiresInHours),
        ]);
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at > now();
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}
