<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $eventService
    ) {}

    /**
     * Show event creation form
     */
    public function create(Request $request): Response
    {
        // Signature already validated by middleware
        // User already authenticated by middleware
        $user = Auth::user();

        // Get or create group from URL parameters (if from a group chat)
        $group = $this->getOrCreateGroup($request, $user);

        // Get user's groups for dropdown
        $userGroups = $user->groups()
            ->where(function($query) {
                $query->where('platform', 'telegram')->where('platform_chat_id', '<', '0')
                    ->orWhere('platform', '!=', 'telegram');
            })
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->platform_chat_title ?: $g->name,
            ]);

        $defaultGroup = $group && ($group->platform !== 'telegram' || (int)$group->platform_chat_id < 0) ? [
            'id' => $group->id,
            'name' => $group->platform_chat_title ?: $group->name,
        ] : null;

        return Inertia::render('Events/Create', [
            'groups' => $userGroups,
            'defaultGroup' => $defaultGroup,
            'user' => [
                'name' => $user->name,
                'telegram_username' => $user->getTelegramService()?->platform_username,
            ],
        ]);
    }

    /**
     * Store new event
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group_id' => 'required|uuid|exists:groups,id',
            'event_date' => 'required|date|after:now',
            'location' => 'nullable|string|max:255',
            'attendance_bonus' => 'required|integer|min:0|max:1000',
            'rsvp_deadline' => 'nullable|date|before:event_date',
            'auto_prompt_hours_after' => 'required|integer|min:0|max:48',
        ]);

        $user = Auth::user();
        $group = Group::findOrFail($validated['group_id']);

        // Create event
        $event = $this->eventService->createEvent($group, $user, $validated);
        $event->load('creator', 'group');

        // Dispatch async event announcement
        \App\Events\EventCreated::dispatch($event);

        return redirect()->route('dashboard.me')->with('success', 'Event created successfully!');
    }

    /**
     * Show event details
     */
    public function show(GroupEvent $event): Response
    {
        $event->load(['creator', 'group', 'rsvps.user', 'attendance.user']);

        $rsvpCounts = $this->eventService->getRsvpCounts($event);

        // Get all group members with their RSVP status
        $groupMembers = $event->group->users()->get()->map(function($user) use ($event) {
            $rsvp = $event->rsvps->firstWhere('user_id', $user->id);
            $attended = $event->attendance->firstWhere('user_id', $user->id);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'telegram_username' => $user->getTelegramService()?->username,
                'rsvp_status' => $rsvp ? $rsvp->response : 'pending',
                'attended' => $attended ? $attended->attended : null,
            ];
        });

        // Transform attendance data
        $attendance = null;
        if ($event->attendance->isNotEmpty()) {
            $attendees = $event->attendance()
                ->where('attended', true)
                ->with('user')
                ->get();

            $attendance = [
                'total_attendees' => $attendees->count(),
                'attendees' => $attendees->map(fn($a) => [
                    'name' => $a->user->name,
                    'telegram_username' => $a->user->getTelegramService()?->username,
                ])->toArray(),
            ];
        }

        $user = Auth::user();

        return Inertia::render('Events/Show', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'event_date' => $event->event_date->toIso8601String(),
                'location' => $event->location,
                'status' => $event->status,
                'attendance_bonus' => $event->attendance_bonus,
                'rsvp_enabled' => $event->rsvp_deadline !== null,
                'rsvp_deadline' => $event->rsvp_deadline?->toIso8601String(),
                'currency' => $event->group->points_currency_name ?? 'points',
                'created_by_user_id' => $event->created_by_user_id,
                'cancelled_at' => $event->cancelled_at?->toIso8601String(),
            ],
            'group' => [
                'id' => $event->group->id,
                'name' => $event->group->name ?? $event->group->platform_chat_title,
            ],
            'isCreator' => $user && $event->created_by_user_id === $user->id,
            'rsvps' => $rsvpCounts,
            'attendance' => $attendance,
            'groupMembers' => $groupMembers,
        ]);
    }

    /**
     * Show attendance recording form
     */
    public function attendance(Request $request, GroupEvent $event): Response
    {
        // Check if already recorded
        if ($event->attendance()->exists()) {
            return redirect()->route('events.show', $event)
                ->with('error', 'Attendance has already been recorded for this event.');
        }

        $groupUsers = $event->group->users()
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'telegram_username' => $u->getTelegramService()?->platform_username,
            ]);

        // Get RSVP data to help with attendance
        $rsvps = $event->rsvps()->with('user')->get()->keyBy('user_id');

        return Inertia::render('Events/Attendance', [
            'event' => $event->load('group'),
            'users' => $groupUsers,
            'rsvps' => $rsvps,
        ]);
    }

    /**
     * Record attendance for event
     */
    public function recordAttendance(Request $request, GroupEvent $event)
    {
        $validated = $request->validate([
            'attendee_ids' => 'required|array',
            'attendee_ids.*' => 'exists:users,id',
        ]);

        $user = Auth::user();

        try {
            $this->eventService->recordAttendance(
                $event,
                $user,
                $validated['attendee_ids']
            );

            // Dispatch async attendance announcement
            \App\Events\AttendanceRecorded::dispatch($event, $user);

            return redirect()->route('dashboard.me')->with('success', 'Attendance recorded successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to record attendance', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Record RSVP
     */
    public function rsvp(Request $request, GroupEvent $event)
    {
        $validated = $request->validate([
            'response' => 'required|in:going,maybe,not_going',
        ]);

        $user = Auth::user();

        $this->eventService->recordRsvp($event, $user, $validated['response']);

        return back()->with('success', 'RSVP updated!');
    }

    /**
     * Helper: Get or create group from request parameters
     */
    private function getOrCreateGroup(Request $request, User $user): ?Group
    {
        $chatId = $request->query('chat_id');
        $chatType = $request->query('chat_type');
        $chatTitle = $request->query('chat_title');

        if (!$chatId || !$chatType) {
            return null;
        }

        // Get platform from user's primary messenger service
        $messengerService = $user->messengerServices()->where('is_primary', true)->first();
        if (!$messengerService) {
            // Fallback to any messenger service
            $messengerService = $user->messengerServices()->first();
        }

        $platform = $messengerService->platform ?? 'telegram';

        $group = Group::where('platform', $platform)
            ->where('platform_chat_id', (string) $chatId)
            ->first();

        if (!$group && in_array($chatType, ['group', 'supergroup'])) {
            $group = Group::create([
                'name' => $chatTitle,
                'platform' => $platform,
                'platform_chat_id' => (string) $chatId,
                'platform_chat_title' => $chatTitle,
                'platform_chat_type' => $chatType,
            ]);
        }

        return $group;
    }

    /**
     * Cancel an event
     */
    public function cancel(Request $request, GroupEvent $event)
    {
        $user = Auth::user();

        try {
            $cancelledEvent = $this->eventService->cancelEvent($event, $user);

            return back()->with([
                'success' => 'Event cancelled successfully.',
                'event' => [
                    'id' => $cancelledEvent->id,
                    'status' => $cancelledEvent->status,
                    'cancelled_at' => $cancelledEvent->cancelled_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }

}
