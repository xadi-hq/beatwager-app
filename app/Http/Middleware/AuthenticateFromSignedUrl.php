<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\MessengerService;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Platform-agnostic authentication middleware using Laravel's signed URLs
 *
 * Handles authentication from signed URLs (Telegram, Discord, Slack, etc.)
 * and establishes Laravel session for subsequent requests.
 *
 * Flow:
 * 1. If user already has session → Continue
 * 2. If valid signed URL with user identifier → Authenticate & establish session
 * 3. Otherwise → Return 401 Unauthorized
 */
class AuthenticateFromSignedUrl
{
    /**
     * Handle an incoming request.
     *
     * Priority:
     * 1. Check if user is already authenticated via session
     * 2. Check for valid signed URL with 'u' parameter (platform:user_id)
     * 3. Otherwise, return 401 Unauthorized
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if user is already authenticated via session
        if (Auth::check()) {
            return $next($request);
        }

        // 2. Check for signed URL with encrypted user identifier
        $hasU = $request->has('u');
        $hasValidSig = $request->hasValidSignature();

        \Log::info('Auth attempt', [
            'has_u' => $hasU,
            'has_valid_signature' => $hasValidSig,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'path' => $request->path(),
        ]);

        if ($hasU && $hasValidSig) {
            try {
                // Decrypt the user identifier (format: "platform:platform_user_id")
                $encryptedUserId = $request->query('u');
                $userId = decrypt($encryptedUserId);

                \Log::info('Decrypted user ID', ['userId' => $userId]);

                // Parse platform and platform_user_id
                if (!is_string($userId) || !str_contains($userId, ':')) {
                    throw new \Exception('Invalid user identifier format');
                }

                [$platform, $platformUserId] = explode(':', $userId, 2);

                \Log::info('Looking up user', [
                    'platform' => $platform,
                    'platformUserId' => $platformUserId,
                ]);

                // Find or create user by platform and platform_user_id
                $username = $request->query('username');
                $firstName = $request->query('first_name');
                $lastName = $request->query('last_name');

                $user = \App\Services\UserMessengerService::findOrCreate(
                    platform: $platform,
                    platformUserId: $platformUserId,
                    userData: [
                        'username' => $username,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ]
                );

                \Log::info('User found/created', ['user_id' => $user->id]);

                // Log the user in and create session (persistent session)
                Auth::login($user, true);

                \Log::info('User authenticated', ['user_id' => $user->id]);

                // Redirect to clean URL without signature parameters
                return $this->redirectToCleanUrl($request);
            } catch (\Exception $e) {
                // Decryption failed or user not found
                \Log::error('Authentication failed from signed URL', [
                    'error' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // 3. No valid authentication method found
        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'Please access this page through a valid authentication link.'
        ], 401);
    }

    /**
     * Redirect to clean URL without authentication parameters
     */
    private function redirectToCleanUrl(Request $request)
    {
        $cleanUrl = $request->url();

        // Remove all signature-related parameters
        $queryParams = $request->except(['u', 'expires', 'signature']);

        if (!empty($queryParams)) {
            $cleanUrl .= '?' . http_build_query($queryParams);
        }

        return redirect($cleanUrl);
    }
}
