<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\MessageContext;
use App\Models\Group;
use Illuminate\Support\Facades\Log;

class ContentGenerator
{
    public function __construct(
        private readonly LLMService $llm,
        private readonly FallbackContentGenerator $fallback,
    ) {}

    public function generate(MessageContext $ctx, ?Group $group): string
    {
        // Try LLM first when configured
        if ($group && $group->llm_api_key) {
            try {
                return $this->llm->generate($ctx, $group);
            } catch (\Throwable $e) {
                Log::warning('LLM content generation failed; using fallback', [
                    'key' => $ctx->key,
                    'group_id' => $group->id,
                    'error' => $e->getMessage(),
                ]);
                
                LogService::llm('fallback_used', $group->id, [
                    'message_key' => $ctx->key,
                    'reason' => 'llm_error',
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            // Log when no LLM configured
            if ($group) {
                LogService::llm('fallback_used', $group->id, [
                    'message_key' => $ctx->key,
                    'reason' => 'no_api_key',
                ]);
            }
        }

        // Fallback
        return $this->fallback->generate($ctx);
    }
}
