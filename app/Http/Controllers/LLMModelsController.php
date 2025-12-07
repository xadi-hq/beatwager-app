<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LLMModelsController extends Controller
{
    /**
     * Minimal fallback models - only used when API call fails and no cache exists.
     * These should be stable, long-lived model aliases that providers maintain.
     */
    private const FALLBACK_MODELS = [
        'anthropic' => [
            ['id' => 'claude-sonnet-4-20250514', 'name' => 'Claude Sonnet 4'],
            ['id' => 'claude-3-5-haiku-latest', 'name' => 'Claude 3.5 Haiku'],
        ],
        'openai' => [
            ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o Mini'],
            ['id' => 'gpt-4o', 'name' => 'GPT-4o'],
        ],
        'requesty' => [
            // Requesty uses provider/model format - include most common options
            ['id' => 'openai/gpt-4o-mini', 'name' => 'OpenAI GPT-4o Mini'],
            ['id' => 'anthropic/claude-sonnet-4-20250514', 'name' => 'Anthropic Claude Sonnet 4'],
        ],
    ];

    /**
     * Cache TTL for model lists (24 hours - models don't change often)
     */
    private const CACHE_TTL = 86400;

    /**
     * Get available models for a provider.
     * Uses system API keys (from .env) for listing models - this is free (no tokens consumed).
     * Group's own API key is only used for actual message generation.
     */
    public function index(Request $request, Group $group): JsonResponse
    {
        // Ensure user is a member of this group
        $userGroup = $group->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userGroup) {
            abort(403, 'You are not a member of this group');
        }

        $provider = $request->query('provider', $group->llm_provider ?? 'anthropic');

        // Check cache first (shared across all groups for same provider)
        $cacheKey = "llm_models:{$provider}";
        $cached = Cache::get($cacheKey);

        if ($cached) {
            return response()->json([
                'models' => $cached,
                'source' => 'cache',
            ]);
        }

        // Get system API key for this provider (used only for listing models - free operation)
        $systemApiKey = $this->getSystemApiKey($provider);

        if (!$systemApiKey) {
            // No system key configured, return fallbacks
            return response()->json([
                'models' => self::FALLBACK_MODELS[$provider] ?? [],
                'source' => 'fallback',
            ]);
        }

        // Try to fetch models from the API using system key
        try {
            $models = match ($provider) {
                'anthropic' => $this->fetchAnthropicModels($systemApiKey),
                'openai' => $this->fetchOpenAIModels($systemApiKey),
                'requesty' => $this->fetchRequestyModels($systemApiKey),
                default => [],
            };

            // Cache successful API response (shared cache for all groups)
            if (!empty($models)) {
                Cache::put($cacheKey, $models, self::CACHE_TTL);
            }

            return response()->json([
                'models' => $models,
                'source' => 'api',
            ]);
        } catch (\Throwable $e) {
            // On error, return fallbacks
            return response()->json([
                'models' => self::FALLBACK_MODELS[$provider] ?? [],
                'source' => 'fallback',
                'error' => 'Could not fetch models from API',
            ]);
        }
    }

    /**
     * Get fallback models (no API key needed)
     */
    public function defaults(Request $request): JsonResponse
    {
        $provider = $request->query('provider', 'anthropic');

        return response()->json([
            'models' => self::FALLBACK_MODELS[$provider] ?? [],
        ]);
    }

    /**
     * Get system API key for a provider (used only for listing models - free operation)
     */
    private function getSystemApiKey(string $provider): ?string
    {
        return match ($provider) {
            'anthropic' => config('services.anthropic.api_key'),
            'openai' => config('services.openai.api_key'),
            'requesty' => config('services.requesty.api_key'),
            default => null,
        };
    }

    private function fetchAnthropicModels(string $apiKey): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])
            ->timeout(10)
            ->get('https://api.anthropic.com/v1/models', [
                'limit' => 100,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch Anthropic models: ' . $response->status());
        }

        $data = $response->json()['data'] ?? [];

        // Filter to Claude models and format
        return collect($data)
            ->filter(fn($model) => str_contains($model['id'], 'claude'))
            ->map(fn($model) => [
                'id' => $model['id'],
                'name' => $model['display_name'] ?? $this->formatModelId($model['id']),
            ])
            ->values()
            ->toArray();
    }

    private function fetchOpenAIModels(string $apiKey): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])
            ->timeout(10)
            ->get('https://api.openai.com/v1/models');

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch OpenAI models: ' . $response->status());
        }

        $data = $response->json()['data'] ?? [];

        // Filter to chat-capable models (gpt-* models that support chat)
        return collect($data)
            ->filter(function ($model) {
                $id = $model['id'];
                // Include GPT models that support chat completions
                // Exclude deprecated, instruct-only, vision-preview, and audio models
                return str_starts_with($id, 'gpt-')
                    && !str_contains($id, 'instruct')
                    && !str_contains($id, 'vision-preview')
                    && !str_contains($id, 'audio')
                    && !str_contains($id, 'realtime');
            })
            ->map(fn($model) => [
                'id' => $model['id'],
                'name' => $this->formatModelId($model['id']),
            ])
            ->unique('id')
            ->sortByDesc('id') // Newer models (higher versions) first
            ->values()
            ->toArray();
    }

    private function fetchRequestyModels(string $apiKey): array
    {
        // Try Requesty's models endpoint
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])
            ->timeout(10)
            ->get('https://router.requesty.ai/v1/models');

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch Requesty models: ' . $response->status());
        }

        $data = $response->json()['data'] ?? [];

        // Format Requesty models (they use provider/model format)
        return collect($data)
            ->filter(function ($model) {
                $id = $model['id'];
                // Include popular providers' chat models
                return str_starts_with($id, 'openai/')
                    || str_starts_with($id, 'anthropic/')
                    || str_starts_with($id, 'google/')
                    || str_starts_with($id, 'mistral/');
            })
            ->map(fn($model) => [
                'id' => $model['id'],
                'name' => $this->formatRequestyModelId($model['id']),
            ])
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    /**
     * Format a model ID into a human-readable name
     */
    private function formatModelId(string $id): string
    {
        // Convert kebab-case to Title Case and clean up
        $name = str_replace(['-', '_'], ' ', $id);
        $name = ucwords($name);

        // Common replacements for readability
        $replacements = [
            'Gpt ' => 'GPT-',
            'Claude ' => 'Claude ',
            'Gemini ' => 'Gemini ',
            '4o' => '4o',
            '3 5' => '3.5',
            ' Mini' => ' Mini',
            ' Latest' => ' (Latest)',
        ];

        foreach ($replacements as $from => $to) {
            $name = str_replace($from, $to, $name);
        }

        return $name;
    }

    /**
     * Format Requesty model ID (provider/model format)
     */
    private function formatRequestyModelId(string $id): string
    {
        $parts = explode('/', $id, 2);
        if (count($parts) !== 2) {
            return $id;
        }

        $provider = ucfirst($parts[0]);
        $model = $this->formatModelId($parts[1]);

        return "{$provider} {$model}";
    }
}
