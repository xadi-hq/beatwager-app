<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Operational logging service for monitoring, performance, and debugging
 * 
 * Separate from audit logs (compliance) - focused on system health and metrics
 */
class LogService
{
    /**
     * Log LLM-related events (generation, caching, errors)
     */
    public static function llm(
        string $event,
        string $groupId,
        array $data = []
    ): void {
        Log::channel('operational')->info("llm.{$event}", [
            'group_id' => $groupId,
            'timestamp' => now()->toIso8601String(),
            ...$data,
        ]);
    }
    
    /**
     * Log performance metrics
     */
    public static function performance(
        string $operation,
        int $durationMs,
        array $context = []
    ): void {
        Log::channel('operational')->info("performance.{$operation}", [
            'duration_ms' => $durationMs,
            'timestamp' => now()->toIso8601String(),
            ...$context,
        ]);
    }
    
    /**
     * Log feature usage
     */
    public static function feature(
        string $feature,
        string $groupId,
        ?string $userId = null,
        array $metadata = []
    ): void {
        Log::channel('operational')->info("feature.{$feature}", [
            'group_id' => $groupId,
            'user_id' => $userId,
            'timestamp' => now()->toIso8601String(),
            ...$metadata,
        ]);
    }
    
    /**
     * Log errors to operational channel (distinct from exception handler)
     */
    public static function error(
        string $category,
        string $message,
        array $context = []
    ): void {
        Log::channel('operational')->error("{$category}.error", [
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            ...$context,
        ]);
    }
}
