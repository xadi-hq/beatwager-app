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
                'is_drop_event' => $msg->is_drop_event,
                'drop_amount' => $msg->drop_amount,
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
            'is_drop_event' => $scheduledMessage->is_drop_event,
            'drop_amount' => $scheduledMessage->drop_amount,
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
            'scheduled_date' => [
                'required',
                'date',
                // Only require future date for non-recurring messages
                // Recurring messages (birthdays, yearly holidays) can have past dates
                function ($attribute, $value, $fail) use ($request) {
                    if (!$request->boolean('is_recurring')) {
                        if (strtotime($value) < strtotime('today')) {
                            $fail('The scheduled date must be today or in the future for one-time messages.');
                        }
                    }
                },
            ],
            'message_template' => 'nullable|string|max:1000',
            'llm_instructions' => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'is_active' => 'boolean',
            'is_drop_event' => 'boolean',
            'drop_amount' => 'nullable|integer|min:1',
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
            'scheduled_date' => [
                'sometimes',
                'date',
                // Only require future date for non-recurring messages
                function ($attribute, $value, $fail) use ($request, $scheduledMessage) {
                    $isRecurring = $request->has('is_recurring')
                        ? $request->boolean('is_recurring')
                        : $scheduledMessage->is_recurring;

                    if (!$isRecurring && strtotime($value) < strtotime('today')) {
                        $fail('The scheduled date must be today or in the future for one-time messages.');
                    }
                },
            ],
            'message_template' => 'nullable|string|max:1000',
            'llm_instructions' => 'nullable|string|max:500',
            'is_recurring' => 'sometimes|boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'is_active' => 'sometimes|boolean',
            'is_drop_event' => 'sometimes|boolean',
            'drop_amount' => 'nullable|integer|min:1',
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

    /**
     * Get birthday suggestions for a group
     */
    public function birthdaySuggestions(Group $group): JsonResponse
    {
        // Ensure user is a member of this group
        $userGroup = $group->users()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userGroup) {
            abort(403, 'You are not a member of this group');
        }

        // Get all group members with their birthdays
        $members = $group->users()
            ->select('users.id', 'users.name', 'users.birthday')
            ->get();

        // Get all birthday scheduled messages for this group
        // Use normalized (lowercase, trimmed) title as key for robust matching
        $scheduledBirthdays = ScheduledMessage::where('group_id', $group->id)
            ->where('message_type', 'birthday')
            ->get()
            ->keyBy(fn($msg) => mb_strtolower(trim($msg->title)));

        $scheduled = [];
        $notScheduled = [];
        $missingBirthday = [];

        foreach ($members as $member) {
            $memberData = [
                'id' => $member->id,
                'name' => $member->name,
                'birthday' => $member->birthday?->toDateString(),
            ];

            // Normalize member name for lookup
            $normalizedName = mb_strtolower(trim($member->name));

            if (!$member->birthday) {
                // Member has no birthday set
                $missingBirthday[] = $memberData;
            } elseif (isset($scheduledBirthdays[$normalizedName])) {
                // Birthday is already scheduled
                $scheduledMessage = $scheduledBirthdays[$normalizedName];
                $scheduled[] = array_merge($memberData, [
                    'scheduled_message_id' => $scheduledMessage->id,
                    'is_active' => $scheduledMessage->is_active,
                ]);
            } else {
                // Birthday exists but not scheduled
                $notScheduled[] = $memberData;
            }
        }

        return response()->json([
            'scheduled' => $scheduled,
            'not_scheduled' => $notScheduled,
            'missing_birthday' => $missingBirthday,
        ]);
    }
}
