<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MessengerService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing Users and their MessengerService connections
 * in a platform-agnostic way (Telegram, Discord, Slack, etc.)
 */
class UserMessengerService
{
    /**
     * Find or create a user and their messenger service connection
     *
     * @param string $platform Platform name (telegram, discord, slack)
     * @param string $platformUserId Platform-specific user ID
     * @param array $userData Additional user data (username, first_name, last_name, photo_url)
     * @return User The user with messenger service loaded
     */
    public static function findOrCreate(
        string $platform,
        string $platformUserId,
        array $userData = []
    ): User {
        return DB::transaction(function () use ($platform, $platformUserId, $userData) {
            // Try to find existing messenger service
            $messengerService = MessengerService::findByPlatform($platform, $platformUserId);

            if ($messengerService) {
                // Update messenger service metadata if changed
                $messengerService->update([
                    'username' => $userData['username'] ?? $messengerService->username,
                    'first_name' => $userData['first_name'] ?? $messengerService->first_name,
                    'last_name' => $userData['last_name'] ?? $messengerService->last_name,
                    'photo_url' => $userData['photo_url'] ?? $messengerService->photo_url,
                ]);

                return $messengerService->user;
            }

            // Create new user and messenger service
            $user = User::create([
                'name' => self::generateUserName($userData),
                'email' => null, // No email for messenger-only users
                'password' => null,
            ]);

            // Create messenger service connection
            MessengerService::create([
                'user_id' => $user->id,
                'platform' => $platform,
                'platform_user_id' => $platformUserId,
                'username' => $userData['username'] ?? null,
                'first_name' => $userData['first_name'] ?? null,
                'last_name' => $userData['last_name'] ?? null,
                'photo_url' => $userData['photo_url'] ?? null,
                'metadata' => $userData['metadata'] ?? null,
            ]);

            // Reload to get messenger service relationship
            return $user->fresh(['messengerServices']);
        });
    }

    /**
     * Generate a display name from user data
     */
    private static function generateUserName(array $userData): string
    {
        if (!empty($userData['first_name']) && !empty($userData['last_name'])) {
            return $userData['first_name'] . ' ' . $userData['last_name'];
        }

        if (!empty($userData['first_name'])) {
            return $userData['first_name'];
        }

        if (!empty($userData['username'])) {
            return $userData['username'];
        }

        return 'User ' . substr(md5(json_encode($userData)), 0, 8);
    }

    /**
     * Find user by platform and platform user ID
     */
    public static function findByPlatform(string $platform, string $platformUserId): ?User
    {
        $messengerService = MessengerService::findByPlatform($platform, $platformUserId);

        return $messengerService?->user;
    }
}
