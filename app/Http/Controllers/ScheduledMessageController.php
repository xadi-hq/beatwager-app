<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\ScheduledMessage;
use App\Services\ScheduledMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduledMessageController extends Controller
{
    public function __construct(
        private ScheduledMessageService $scheduledMessageService
    ) {}

    /**
     * List all scheduled messages for a group
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

        $upcomingOnly = $request->boolean('upcoming_only', false);
        $filters = $upcomingOnly ? ['upcoming_only' => true] : [];
        $messages = $this->scheduledMessageService->getForGroup($group, $filters);

        return response()->json([
            'messages' => $messages->map(fn($msg) => [
                'id' => $msg->id,
                'message_type' => $msg->message_type,
                'title' => $msg->title,
                'scheduled_date' => $msg->scheduled_date->toDateString(),
                'is_recurring' => $msg->is_recurring,
                'recurrence_type' => $msg->recurrence_type,
                'is_active' => $msg->is_active,
                'last_sent_at' => $msg->last_sent_at?->toIso8601String(),
                'next_occurrence' => $msg->getNextOccurrence()?->toDateString(),
            ]),
        ]);
    }

    /**
     * Get a specific scheduled message
     */
    public function show(Group $group, ScheduledMessage $scheduledMessage): JsonResponse
    {
        // Ensure user is a member of this group
        $userGroup = $group->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userGroup) {
            abort(403, 'You are not a member of this group');
        }

        if ($scheduledMessage->group_id !== $group->id) {
            abort(404, 'Scheduled message not found for this group');
        }

        return response()->json([
            'id' => $scheduledMessage->id,
            'message_type' => $scheduledMessage->message_type,
            'title' => $scheduledMessage->title,
            'scheduled_date' => $scheduledMessage->scheduled_date->toDateString(),
            'message_template' => $scheduledMessage->message_template,
            'llm_instructions' => $scheduledMessage->llm_instructions,
            'is_recurring' => $scheduledMessage->is_recurring,
            'recurrence_type' => $scheduledMessage->recurrence_type,
            'is_active' => $scheduledMessage->is_active,
            'last_sent_at' => $scheduledMessage->last_sent_at?->toIso8601String(),
        ]);
    }

    /**
     * Create a new scheduled message
     */
    public function store(Request $request, Group $group): JsonResponse
    {
        // Ensure user is a member of this group
        $userGroup = $group->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userGroup) {
            abort(403, 'You are not a member of this group');
        }

        $validated = $request->validate([
            'message_type' => 'required|in:holiday,birthday,custom',
            'title' => 'required|string|max:255',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'message_template' => 'nullable|string|max:1000',
            'llm_instructions' => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'is_active' => 'boolean',
        ]);

        $message = $this->scheduledMessageService->create($group, $validated);

        return response()->json([
            'message' => 'Scheduled message created successfully',
            'scheduled_message' => $message,
        ], 201);
    }

    /**
     * Update an existing scheduled message
     */
    public function update(Request $request, Group $group, ScheduledMessage $scheduledMessage): JsonResponse
    {
        // Ensure user is a member of this group
        $userGroup = $group->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userGroup) {
            abort(403, 'You are not a member of this group');
        }

        if ($scheduledMessage->group_id !== $group->id) {
            abort(404, 'Scheduled message not found for this group');
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'scheduled_date' => 'sometimes|date',
            'message_template' => 'nullable|string|max:1000',
            'llm_instructions' => 'nullable|string|max:500',
            'is_recurring' => 'sometimes|boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'is_active' => 'sometimes|boolean',
        ]);

        $message = $this->scheduledMessageService->update($scheduledMessage, $validated);

        return response()->json([
            'message' => 'Scheduled message updated successfully',
            'scheduled_message' => $message,
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Group $group, ScheduledMessage $scheduledMessage): JsonResponse
    {
        // Ensure user is a member of this group
        $userGroup = $group->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userGroup) {
            abort(403, 'You are not a member of this group');
        }

        if ($scheduledMessage->group_id !== $group->id) {
            abort(404, 'Scheduled message not found for this group');
        }

        $message = $this->scheduledMessageService->toggleActive($scheduledMessage);

        return response()->json([
            'message' => 'Status updated successfully',
            'is_active' => $message->is_active,
        ]);
    }

    /**
     * Delete a scheduled message
     */
    public function destroy(Group $group, ScheduledMessage $scheduledMessage): JsonResponse
    {
        // Ensure user is a member of this group
        $userGroup = $group->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userGroup) {
            abort(403, 'You are not a member of this group');
        }

        if ($scheduledMessage->group_id !== $group->id) {
            abort(404, 'Scheduled message not found for this group');
        }

        $this->scheduledMessageService->delete($scheduledMessage);

        return response()->json([
            'message' => 'Scheduled message deleted successfully',
        ]);
    }
}
