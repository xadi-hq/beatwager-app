<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShortUrl extends Model
{
    protected $fillable = [
        'code',
        'target_url',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a unique short code
     */
    public static function generateUniqueCode(int $length = 6): string
    {
        do {
            $code = self::generateCode($length);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Generate a random alphanumeric code
     */
    private static function generateCode(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $code;
    }

    /**
     * Check if the short URL has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Scope to get only active (non-expired) short URLs
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}