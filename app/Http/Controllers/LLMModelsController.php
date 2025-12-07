<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LLMModelsController extends Controller
{
    /**
     * Default models for each provider (used as fallback)
     */
    private const DEFAULT_MODELS = [
        'anthropic' => [
            ['id' => 'claude-3-haiku-20240307', 'name' => 'Claude 3 Haiku (Fast)'],
            ['id' => 'claude-3-5-haiku-latest', 'name' => 'Claude 3.5 Haiku (Fast)'],
            ['id' => 'claude-3-5-sonnet-latest', 'name' => 'Claude 3.5 Sonnet (Balanced)'],
            ['id' => 'claude-sonnet-4-20250514', 'name' => 'Claude Sonnet 4 (Latest)'],
        ],
        'openai' => [
            ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o Mini (Fast & Cheap)'],
            ['id' => 'gpt-4o', 'name' => 'GPT-4o (Balanced)'],
            ['id' => 'gpt-4-turbo', 'name' => 'GPT-4 Turbo'],
        ],
        'requesty' => [
            ['id' => 'openai/gpt-4o-mini', 'name' => 'OpenAI GPT-4o Mini'],
            ['id' => 'openai/gpt-4o', 'name' => 'OpenAI GPT-4o'],
            ['id' => 'anthropic/claude-3-haiku-20240307', 'name' => 'Anthropic Claude 3 Haiku'],
            ['id' => 'anthropic/claude-3-5-sonnet-latest', 'name' => 'Anthropic Claude 3.5 Sonnet'],
            ['id' => 'google/gemini-1.5-flash', 'name' => 'Google Gemini 1.5 Flash'],
            ['id' => 'google/gemini-1.5-pro', 'name' => 'Google Gemini 1.5 Pro'],
        ],
    ];

    /**
     * Get available models for a provider
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

        // If no API key configured, return default models
        if (empty($group->llm_api_key)) {
            return response()->json([
                'models' => self::DEFAULT_MODELS[$provider] ?? [],
                'source' => 'defaults',
            ]);
        }

        // Try to fetch models from the API
        try {
            $models = match ($provider) {
                'anthropic' => $this->fetchAnthropicModels($group->llm_api_key),
                'openai' => $this->fetchOpenAIModels($group->llm_api_key),
                'requesty' => self::DEFAULT_MODELS['requesty'], // Requesty doesn't have a models endpoint
                default => [],
            };

            return response()->json([
                'models' => $models,
                'source' => $provider === 'requesty' ? 'defaults' : 'api',
            ]);
        } catch (\Throwable $e) {
            // On error, return defaults
            return response()->json([
                'models' => self::DEFAULT_MODELS[$provider] ?? [],
                'source' => 'defaults',
                'error' => 'Could not fetch models from API',
            ]);
        }
    }

    /**
     * Get default models (no API key needed)
     */
    public function defaults(Request $request): JsonResponse
    {
        $provider = $request->query('provider', 'anthropic');

        return response()->json([
            'models' => self::DEFAULT_MODELS[$provider] ?? [],
        ]);
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
            throw new \RuntimeException('Failed to fetch Anthropic models');
        }

        $data = $response->json()['data'] ?? [];

        // Filter and format models suitable for chat
        return collect($data)
            ->filter(fn($model) => str_contains($model['id'], 'claude'))
            ->map(fn($model) => [
                'id' => $model['id'],
                'name' => $model['display_name'] ?? $model['id'],
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
            throw new \RuntimeException('Failed to fetch OpenAI models');
        }

        $data = $response->json()['data'] ?? [];

        // Filter to only chat-capable models and format nicely
        $chatModels = ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo'];

        return collect($data)
            ->filter(function ($model) use ($chatModels) {
                foreach ($chatModels as $prefix) {
                    if (str_starts_with($model['id'], $prefix)) {
                        return true;
                    }
                }
                return false;
            })
            ->map(fn($model) => [
                'id' => $model['id'],
                'name' => $this->formatOpenAIModelName($model['id']),
            ])
            ->unique('id')
            ->sortBy('id')
            ->values()
            ->toArray();
    }

    private function formatOpenAIModelName(string $id): string
    {
        // Format model IDs into human-readable names
        $mappings = [
            'gpt-4o-mini' => 'GPT-4o Mini (Fast & Cheap)',
            'gpt-4o' => 'GPT-4o (Balanced)',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Legacy)',
        ];

        foreach ($mappings as $prefix => $name) {
            if (str_starts_with($id, $prefix)) {
                if ($id === $prefix) {
                    return $name;
                }
                // Include version info for dated models
                return $name . ' (' . substr($id, strlen($prefix) + 1) . ')';
            }
        }

        return $id;
    }
}
