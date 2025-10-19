<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\ScheduledMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduledMessageService
{
    /**
     * Get all scheduled messages for a group (or all groups if null)
     */
    public function getForGroup(?Group $group = null, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $group
            ? $group->scheduledMessages()
            : ScheduledMessage::query();

        $query->orderBy('scheduled_date', 'asc');

        // Apply filters
        if (isset($filters['upcoming_only']) && $filters['upcoming_only']) {
            $query->where(function ($q) {
                $q->where('scheduled_date', '>=', now()->toDateString())
                    ->orWhere('is_recurring', true);
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->get();
    }

    /**
     * Create a new scheduled message
     */
    public function create(Group $group, array $data): ScheduledMessage
    {
        $message = ScheduledMessage::create([
            'group_id' => $group->id,
            'message_type' => $data['message_type'] ?? 'custom',
            'title' => $data['title'],
            'scheduled_date' => $data['scheduled_date'],
            'message_template' => $data['message_template'] ?? null,
            'llm_instructions' => $data['llm_instructions'] ?? null,
            'is_recurring' => $data['is_recurring'] ?? false,
            'recurrence_type' => $data['recurrence_type'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        Log::channel('operational')->info('scheduled_message.created', [
            'group_id' => $group->id,
            'message_id' => $message->id,
            'title' => $message->title,
            'scheduled_date' => $message->scheduled_date->toDateString(),
            'is_recurring' => $message->is_recurring,
        ]);

        return $message;
    }

    /**
     * Update an existing scheduled message
     */
    public function update(ScheduledMessage $message, array $data): ScheduledMessage
    {
        $message->update([
            'title' => $data['title'] ?? $message->title,
            'scheduled_date' => $data['scheduled_date'] ?? $message->scheduled_date,
            'message_template' => $data['message_template'] ?? $message->message_template,
            'llm_instructions' => $data['llm_instructions'] ?? $message->llm_instructions,
            'is_recurring' => $data['is_recurring'] ?? $message->is_recurring,
            'recurrence_type' => $data['recurrence_type'] ?? $message->recurrence_type,
            'is_active' => $data['is_active'] ?? $message->is_active,
        ]);

        Log::channel('operational')->info('scheduled_message.updated', [
            'group_id' => $message->group_id,
            'message_id' => $message->id,
            'title' => $message->title,
        ]);

        return $message->fresh();
    }

    /**
     * Toggle active status
     */
    public function toggleActive(ScheduledMessage $message): ScheduledMessage
    {
        $message->update(['is_active' => !$message->is_active]);

        Log::channel('operational')->info('scheduled_message.toggled', [
            'group_id' => $message->group_id,
            'message_id' => $message->id,
            'is_active' => $message->is_active,
        ]);

        return $message->fresh();
    }

    /**
     * Delete a scheduled message
     */
    public function delete(ScheduledMessage $message): void
    {
        Log::channel('operational')->info('scheduled_message.deleted', [
            'group_id' => $message->group_id,
            'message_id' => $message->id,
            'title' => $message->title,
        ]);

        $message->delete();
    }

    /**
     * Get all messages that should be sent today
     */
    public function getMessagesToSendToday(): \Illuminate\Database\Eloquent\Collection
    {
        return ScheduledMessage::where('is_active', true)
            ->with('group')
            ->get()
            ->filter(fn($message) => $message->shouldSendToday());
    }

    /**
     * Mark a message as sent
     */
    public function markAsSent(ScheduledMessage $message): void
    {
        $message->update(['last_sent_at' => now()]);

        Log::channel('operational')->info('scheduled_message.sent', [
            'group_id' => $message->group_id,
            'message_id' => $message->id,
            'title' => $message->title,
            'sent_at' => now()->toDateTimeString(),
        ]);
    }
}
