<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\MessageContext;
use App\Models\Group;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LLMService
{
    private const CACHE_TTL = 3600; // 1 hour

    public function generate(MessageContext $ctx, Group $group): string
    {
        $startTime = microtime(true);
        
        // Try cache first
        $cacheKey = $this->getCacheKey($ctx, $group);
        if ($cached = Cache::get($cacheKey)) {
            LogService::llm('generation.cached', $group->id, [
                'message_key' => $ctx->key,
                'provider' => $group->llm_provider ?? 'anthropic',
            ]);
            return $cached;
        }

        // Build prompts
        $systemPrompt = $this->buildSystemPrompt($group);
        $userPrompt = $this->buildUserPrompt($ctx);
        $provider = $group->llm_provider ?? 'anthropic';

        try {
            // Call LLM
            $response = $this->callProvider(
                $provider,
                $group->llm_api_key,
                $systemPrompt,
                $userPrompt
            );

            // Validate and cache
            $validated = $this->validateResponse($response, $ctx);
            Cache::put($cacheKey, $validated, self::CACHE_TTL);
            
            $duration = (int)((microtime(true) - $startTime) * 1000);
            
            // Estimate cost (rough approximation)
            $estimatedTokens = (int)((strlen($systemPrompt) + strlen($userPrompt) + strlen($validated)) / 4);
            $costPerMillion = $provider === 'anthropic' ? 0.25 : 0.50;
            $estimatedCost = ($estimatedTokens / 1_000_000) * $costPerMillion;
            
            LogService::llm('generation.success', $group->id, [
                'message_key' => $ctx->key,
                'provider' => $provider,
                'duration_ms' => $duration,
                'estimated_tokens' => $estimatedTokens,
                'estimated_cost_usd' => round($estimatedCost, 6),
            ]);

            return $validated;
        } catch (\Throwable $e) {
            $duration = (int)((microtime(true) - $startTime) * 1000);
            
            LogService::error('llm', $e->getMessage(), [
                'group_id' => $group->id,
                'message_key' => $ctx->key,
                'provider' => $provider,
                'duration_ms' => $duration,
            ]);
            
            throw $e;
        }
    }

    private function buildSystemPrompt(Group $group): string
    {
        $groupType = $group->group_type ?? 'friends';
        $botTone = $group->bot_tone ?? 'friendly and encouraging';

        return <<<PROMPT
You are BeatWager bot, managing social wagers for a {$groupType} group.

Your personality: {$botTone}

Guidelines:
- Be concise (2-3 sentences max)
- Include relevant emojis
- Maintain the specified tone consistently
- Preserve all factual information (numbers, dates, names)
- Create excitement and engagement
- Never mention being an AI or technical details

PROMPT;
    }

    private function buildUserPrompt(MessageContext $ctx): string
    {
        $meta = __('messages.' . $ctx->key);
        $intent = $meta['intent'] ?? '';
        $toneHints = $meta['tone_hints'] ?? [];

        // Flatten data for prompt
        $dataStr = json_encode($ctx->data, JSON_PRETTY_PRINT);

        return <<<PROMPT
Generate a message with personality for this context:

Intent: {$intent}
Tone hints: {$this->formatList($toneHints)}

Required fields to include: {$this->formatList($ctx->requiredFields)}

Data:
{$dataStr}

Rules:
- Include ALL required fields in natural language
- Keep under 250 words
- Use appropriate emojis
- Match the tone hints

Generate the message:
PROMPT;
    }

    private function callProvider(
        string $provider,
        string $apiKey,
        string $systemPrompt,
        string $userPrompt
    ): string {
        return match ($provider) {
            'anthropic' => $this->callAnthropic($apiKey, $systemPrompt, $userPrompt),
            'openai' => $this->callOpenAI($apiKey, $systemPrompt, $userPrompt),
            default => throw new \InvalidArgumentException("Unknown provider: {$provider}")
        };
    }

    private function callAnthropic(string $apiKey, string $system, string $user): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])
            ->timeout(10)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 300,
                'system' => $system,
                'messages' => [
                    ['role' => 'user', 'content' => $user]
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Anthropic API error: ' . $response->body());
        }

        return $response->json()['content'][0]['text'];
    }

    private function callOpenAI(string $apiKey, string $system, string $user): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(10)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'max_tokens' => 300,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        return $response->json()['choices'][0]['message']['content'];
    }

    private function validateResponse(string $response, MessageContext $ctx): string
    {
        // Basic validation
        $length = strlen($response);
        if ($length < 20 || $length > 1000) {
            throw new \RuntimeException('Response length invalid: ' . $length);
        }

        // Ensure not empty
        if (trim($response) === '') {
            throw new \RuntimeException('Empty response from LLM');
        }

        return trim($response);
    }

    private function getCacheKey(MessageContext $ctx, Group $group): string
    {
        // Create cache key from context + tone
        $dataHash = md5(json_encode($ctx->data));
        return sprintf(
            'llm:%s:%s:%s:%s',
            $group->id,
            $ctx->key,
            $dataHash,
            md5($group->bot_tone ?? '')
        );
    }

    private function formatList(array $items): string
    {
        return empty($items) ? 'none' : implode(', ', $items);
    }
}
