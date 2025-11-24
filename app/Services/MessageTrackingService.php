<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\SentMessage;
use Carbon\Carbon;

class MessageTrackingService
{
    /**
     * Anti-spam rules configuration
     */
    private const RULES = [
        'engagement.prompt' => [
            'max_per_context_per_day' => 1,      // Only 1 prompt per wager per day
            'cooldown_hours' => 24,              // 24h between prompts
        ],
        'birthday.reminder' => [
            'max_per_context_per_day' => 1,      // Only 1 reminder per birthday per day
        ],
        'weekly.recap' => [
            'max_per_group_per_week' => 1,       // Only 1 recap per week
        ],
        'decay.warning' => [
            'max_per_user_per_week' => 1,        // Only 1 decay warning per user per week
        ],
        // Add more as needed
    ];

    /**
     * Check if a message can be sent based on anti-spam rules
     */
    public function canSendMessage(
        Group $group,
        string $messageType,
        ?string $contextType = null,
        ?string $contextId = null
    ): bool {
        $rules = self::RULES[$messageType] ?? [];

        if (empty($rules)) {
            // No rules defined = always allow
            return true;
        }

        // Check max_per_context_per_day
        if (isset($rules['max_per_context_per_day']) && $contextId) {
            $count = SentMessage::where('group_id', $group->id)
                ->where('message_type', $messageType)
                ->where('context_id', $contextId)
                ->where('sent_at', '>=', now()->subDay())
                ->count();

            if ($count >= $rules['max_per_context_per_day']) {
                return false;
            }
        }

        // Check cooldown_hours
        if (isset($rules['cooldown_hours']) && $contextId) {
            $lastSent = SentMessage::where('group_id', $group->id)
                ->where('message_type', $messageType)
                ->where('context_id', $contextId)
                ->latest('sent_at')
                ->first();

            if ($lastSent && $lastSent->sent_at->diffInHours(now()) < $rules['cooldown_hours']) {
                return false;
            }
        }

        // Check max_per_group_per_week
        if (isset($rules['max_per_group_per_week'])) {
            $count = SentMessage::where('group_id', $group->id)
                ->where('message_type', $messageType)
                ->where('sent_at', '>=', now()->subWeek())
                ->count();

            if ($count >= $rules['max_per_group_per_week']) {
                return false;
            }
        }

        // Check max_per_user_per_week
        if (isset($rules['max_per_user_per_week']) && $contextId && $contextType === 'user') {
            $count = SentMessage::where('group_id', $group->id)
                ->where('message_type', $messageType)
                ->where('context_type', 'user')
                ->where('context_id', $contextId)
                ->where('sent_at', '>=', now()->subWeek())
                ->count();

            if ($count >= $rules['max_per_user_per_week']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Record that a message was sent
     */
    public function recordMessage(
        Group $group,
        string $messageType,
        ?string $summary = null,
        ?string $contextType = null,
        ?string $contextId = null,
        ?array $metadata = null
    ): SentMessage {
        return SentMessage::create([
            'group_id' => $group->id,
            'message_type' => $messageType,
            'context_type' => $contextType,
            'context_id' => $contextId,
            'summary' => $summary,
            'metadata' => $metadata,
            'sent_at' => now(),
        ]);
    }

    /**
     * Get recent message history for LLM context
     * "Remember last week's Marathon Bet?"
     */
    public function getRecentHistory(Group $group, int $days = 7): array
    {
        $messages = SentMessage::where('group_id', $group->id)
            ->where('sent_at', '>=', now()->subDays($days))
            ->orderBy('sent_at', 'desc')
            ->limit(10)
            ->get();

        return $messages->map(fn($msg) => [
            'type' => $msg->message_type,
            'summary' => $msg->summary,
            'date' => $msg->sent_at->diffForHumans(),
        ])->toArray();
    }
}
