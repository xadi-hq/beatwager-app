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
        $allowNsfw = $group->allow_nsfw ?? false;

        $contentGuidelines = $allowNsfw
            ? '- No content restrictions'
            : '- Keep content appropriate for all ages (PG-13)
- Avoid explicit language, profanity, or adult themes
- Use creative alternatives for strong language';

        return <<<PROMPT
You are BeatWager bot, managing social wagers for a {$groupType} group.

Your personality: {$botTone}

Guidelines:
- Be VERY concise (1-2 sentences max, 30 words or less)
- Include 1-2 relevant emojis only
- Maintain the specified tone consistently
- Preserve all factual information (numbers, dates, names, currency)
- Create excitement without being verbose
- Never mention being an AI or technical details
- Get straight to the point

Content guidelines:
{$contentGuidelines}

PROMPT;
    }

    private function buildUserPrompt(MessageContext $ctx): string
    {
        $meta = __('messages.' . $ctx->key);
        $intent = $meta['intent'] ?? '';
        $toneHints = $meta['tone_hints'] ?? [];
        $maxWords = $meta['max_words'] ?? 30;  // Default to 30 if not specified

        // Flatten data for prompt
        $dataStr = json_encode($ctx->data, JSON_PRETTY_PRINT);

        // Adjust emoji and style guidance based on length
        $emojiGuidance = $maxWords > 100 ? '2-3 emojis' : '1-2 emojis maximum';
        $styleGuidance = $maxWords > 100
            ? 'Be comprehensive and detailed'
            : 'Be direct and punchy';

        // Build engagement triggers hint if present
        $triggersHint = '';
        if (isset($ctx->data['triggers']) && is_array($ctx->data['triggers'])) {
            $activeTriggersHint = $this->buildTriggersGuidance($ctx->data['triggers']);
            if ($activeTriggersHint) {
                $triggersHint = "\n\nEngagement Context (use for extra personality):\n{$activeTriggersHint}";
            }
        }

        return <<<PROMPT
Generate a message with personality for this context:

Intent: {$intent}
Tone hints: {$this->formatList($toneHints)}

Required fields to include: {$this->formatList($ctx->requiredFields)}

Data:
{$dataStr}{$triggersHint}

Rules:
- Include ALL required fields in natural language
- Keep under {$maxWords} words total
- Use {$emojiGuidance}
- Match the tone hints
- {$styleGuidance}
- If engagement triggers are present, use them to add personality and create FOMO

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
            'openai', 'requesty' => $this->callOpenAI($apiKey, $systemPrompt, $userPrompt, $provider),
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

    private function callOpenAI(string $apiKey, string $system, string $user, string $provider = 'openai'): string
    {
        // Determine the API endpoint and model format based on provider
        [$endpoint, $model] = match ($provider) {
            'requesty' => [
                'https://router.requesty.ai/v1/chat/completions',
                'openai/gpt-4o-mini'  // Requesty expects provider/model format
            ],
            default => [
                'https://api.openai.com/v1/chat/completions',
                'gpt-4o-mini'
            ],
        };

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(10)
            ->post($endpoint, [
                'model' => $model,
                'max_tokens' => 300,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException("{$provider} API error: " . $response->body());
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
        // Create cache key from context + tone + content settings
        $dataHash = md5(json_encode($ctx->data));
        $settingsHash = md5(($group->bot_tone ?? '') . ($group->allow_nsfw ? '1' : '0'));
        return sprintf(
            'llm:%s:%s:%s:%s',
            $group->id,
            $ctx->key,
            $dataHash,
            $settingsHash
        );
    }

    private function formatList(array $items): string
    {
        return empty($items) ? 'none' : implode(', ', $items);
    }

    /**
     * Build human-readable guidance from engagement triggers
     */
    private function buildTriggersGuidance(array $triggers): string
    {
        $hints = [];

        // Position triggers
        if ($triggers['is_first'] ?? false) {
            $hints[] = "- First person to join this wager (trendsetter!)";
        }
        if ($triggers['is_leader'] ?? false) {
            $hints[] = "- This user is #1 on the leaderboard (the leader makes their move!)";
        }
        if ($triggers['is_underdog'] ?? false) {
            $hints[] = "- This user is in the bottom 25% (underdog fighting back!)";
        }

        // Stakes triggers
        if ($triggers['is_high_stakes'] ?? false) {
            $percentage = $triggers['stake_percentage'] ?? 50;
            $hints[] = "- Wagering {$percentage}% of their balance (high stakes/all-in energy!)";
        }

        // Comeback triggers
        if ($triggers['is_comeback'] ?? false) {
            $days = $triggers['days_inactive'] ?? null;
            if ($days) {
                $hints[] = "- Back after {$days} days of inactivity (comeback story! welcome back!)";
            } else {
                $hints[] = "- Recently had points decay (comeback after tough times!)";
            }
        }

        // Momentum triggers
        if ($triggers['is_contrarian'] ?? false) {
            $hints[] = "- Betting against the majority (bold contrarian move!)";
        }
        if ($triggers['is_bandwagon'] ?? false) {
            $hints[] = "- Joining the majority side (piling on with the crowd!)";
        }

        // Timing triggers
        if ($triggers['is_last_minute'] ?? false) {
            $hours = $triggers['hours_to_deadline'] ?? null;
            $hints[] = "- Joining with only {$hours} hours left (last-minute decision!)";
        }
        if ($triggers['is_early_bird'] ?? false) {
            $hints[] = "- Joined within an hour of wager creation (quick on the draw!)";
        }

        return empty($hints) ? '' : implode("\n", $hints);
    }
}
