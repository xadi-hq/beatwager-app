<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\GroupEventAttendance;
use App\Models\GroupEventRsvp;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function __construct(
        private readonly PointService $pointService
    ) {}

    /**
     * Create a new group event
     */
    public function createEvent(Group $group, User $creator, array $data): GroupEvent
    {
        return DB::transaction(function () use ($group, $creator, $data) {
            $event = GroupEvent::create([
                'group_id' => $group->id,
                'created_by_user_id' => $creator->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'event_date' => $data['event_date'],
                'location' => $data['location'] ?? null,
                'attendance_bonus' => $data['attendance_bonus'] ?? 100,
                'rsvp_deadline' => $data['rsvp_deadline'] ?? null,
                'auto_prompt_hours_after' => $data['auto_prompt_hours_after'] ?? 2,
                'status' => 'upcoming',
            ]);

            return $event;
        });
    }

    /**
     * Record RSVP for an event
     *
     * @return array{rsvp: GroupEventRsvp, previous_response: string|null, was_changed: bool}
     */
    public function recordRsvp(GroupEvent $event, User $user, string $response): array
    {
        // Get existing RSVP to detect changes
        $existingRsvp = GroupEventRsvp::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        $previousResponse = $existingRsvp?->response;

        $rsvp = GroupEventRsvp::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $user->id],
            ['response' => $response]
        );

        return [
            'rsvp' => $rsvp,
            'previous_response' => $previousResponse,
            'was_changed' => $previousResponse !== null && $previousResponse !== $response,
        ];
    }

    /**
     * Get RSVP counts for an event
     */
    public function getRsvpCounts(GroupEvent $event): array
    {
        $rsvps = $event->rsvps()->get();

        return [
            'going' => $rsvps->where('response', 'going')->count(),
            'maybe' => $rsvps->where('response', 'maybe')->count(),
            'not_going' => $rsvps->where('response', 'not_going')->count(),
            'no_response' => $event->group->users()->count() - $rsvps->count(),
        ];
    }

    /**
     * Record attendance for an event
     * Anyone can submit, first submission wins
     */
    public function recordAttendance(GroupEvent $event, User $reporter, array $attendeeIds): void
    {
        DB::transaction(function () use ($event, $reporter, $attendeeIds) {
            // Check if already recorded
            if ($event->attendance()->exists()) {
                throw new \Exception('Attendance already recorded for this event');
            }

            // Prevent single-attendee point farming
            $attendeeCount = count($attendeeIds);
            if ($attendeeCount < 2) {
                throw new \Exception('Events require at least 2 attendees to award bonuses. Got: ' . $attendeeCount);
            }

            $groupUsers = $event->group->users()->get();

            foreach ($groupUsers as $user) {
                $attended = in_array($user->id, $attendeeIds);

                GroupEventAttendance::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'attended' => $attended,
                    'reported_by_user_id' => $reporter->id,
                    'reported_at' => now(),
                    'bonus_awarded' => false,
                ]);

                // Award bonus to attendees
                if ($attended) {
                    $this->awardAttendanceBonus($user, $event);
                }
            }

            // Update event status
            $event->update(['status' => 'completed']);
        });
    }

    /**
     * Award attendance bonus and mark as awarded
     */
    private function awardAttendanceBonus(User $user, GroupEvent $event): void
    {
        // Load streak config
        $streakConfig = \App\Models\EventStreakConfig::where('group_id', $event->group_id)->first();

        // Calculate current streak
        $pivot = DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $event->group_id)
            ->first();

        $currentStreak = $pivot->event_attendance_streak ?? 0;

        // Check if streak should continue or reset
        $lastAttended = $pivot->last_event_attended_at
            ? \Carbon\Carbon::parse($pivot->last_event_attended_at)
            : null;

        // Determine if this is consecutive attendance
        // Logic: Check if there were any completed events between last attendance and this one where user didn't attend
        if ($lastAttended) {
            $missedEvents = GroupEvent::where('group_id', $event->group_id)
                ->where('status', 'completed')
                ->where('event_date', '>', $lastAttended)
                ->where('event_date', '<', $event->event_date)
                ->whereDoesntHave('attendance', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('attended', true);
                })
                ->exists();

            if ($missedEvents) {
                $currentStreak = 0; // Reset streak
            }
        }

        // Increment streak
        $newStreak = $currentStreak + 1;

        // Get multiplier
        $multiplier = $streakConfig ? $streakConfig->getMultiplierForStreak($newStreak) : 1.0;

        // Calculate final bonus
        $baseBonus = $event->attendance_bonus;
        $finalBonus = (int) ($baseBonus * $multiplier);

        // Award points with metadata
        $this->pointService->awardPoints(
            $user,
            $event->group,
            $finalBonus,
            'event_attendance_bonus',
            $event
        );

        // Update streak tracking
        DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $event->group_id)
            ->update([
                'event_attendance_streak' => $newStreak,
                'last_event_attended_at' => $event->event_date,
                'last_wager_joined_at' => now(), // Counts as activity for decay
            ]);

        // Mark bonus as awarded with streak info
        GroupEventAttendance::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->update([
                'bonus_awarded' => true,
                'streak_at_time' => $newStreak,
                'multiplier_applied' => $multiplier,
            ]);
    }

    /**
     * Get events for display on dashboard
     */
    public function getEventsForGroup(Group $group): array
    {
        return [
            'upcoming' => $group->events()
                ->active()  // Exclude expired events with no RSVPs
                ->where('status', 'upcoming')
                ->where('event_date', '>=', now())
                ->orderBy('event_date')
                ->get(),
            'past_processed' => $group->events()
                ->where('status', 'completed')
                ->orderByDesc('event_date')
                ->limit(10)
                ->get(),
            'past_unprocessed' => $group->events()
                ->where('status', 'upcoming')
                ->where('event_date', '<', now())
                ->orderByDesc('event_date')
                ->get(),
        ];
    }

    /**
     * Get events that need attendance prompts
     */
    public function getEventsPendingAttendancePrompt(): array
    {
        $events = GroupEvent::where('status', 'upcoming')
            ->whereNotNull('event_date')
            ->get();

        return $events->filter(function ($event) {
            return $event->needsAttendancePrompt();
        })->all();
    }

    /**
     * Get events that need RSVP reminders
     */
    public function getEventsPendingRsvpReminder(): array
    {
        $events = GroupEvent::where('status', 'upcoming')
            ->whereNotNull('event_date')
            ->where('event_date', '>', now()) // Only upcoming events
            ->get();

        return $events->filter(function ($event) {
            return $event->needsRsvpReminder();
        })->all();
    }

    /**
     * Mark event as expired (no attendance recorded)
     */
    public function markEventAsExpired(GroupEvent $event): void
    {
        $event->update(['status' => 'expired']);
    }

    /**
     * Cancel an event
     *
     * @throws \Exception if event cannot be cancelled
     */
    public function cancelEvent(GroupEvent $event, User $user): GroupEvent
    {
        if ($event->created_by_user_id !== $user->id) {
            throw new \Exception('Only the creator can cancel the event');
        }

        if ($event->status !== 'upcoming') {
            throw new \Exception('Only upcoming events can be cancelled');
        }

        // Check if event already started
        if ($event->event_date->isPast()) {
            throw new \Exception('Cannot cancel an event that has already started');
        }

        return DB::transaction(function () use ($event, $user) {
            $event->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by_user_id' => $user->id,
            ]);

            // Dispatch event cancelled notification
            \App\Events\EventCancelled::dispatch($event);

            return $event->load(['group', 'creator', 'cancelledBy']);
        });
    }
}
