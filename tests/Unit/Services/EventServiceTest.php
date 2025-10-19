<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\GroupEventAttendance;
use App\Models\GroupEventRsvp;
use App\Models\User;
use App\Services\EventService;
use App\Services\PointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    private EventService $eventService;
    private PointService $pointService;
    private Group $group;
    private User $creator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pointService = $this->createMock(PointService::class);
        $this->eventService = new EventService($this->pointService);

        // Create test data
        $this->group = Group::factory()->create([
            'starting_balance' => 1000,
        ]);

        $this->creator = User::factory()->create();
        $this->group->users()->attach($this->creator->id, [
            'id' => Str::uuid(),
            'points' => 1000,
            'role' => 'admin',
        ]);
    }

    public function test_creates_event_with_required_fields(): void
    {
        $eventData = [
            'name' => 'Test Event',
            'event_date' => Carbon::now()->addDays(7),
        ];

        $event = $this->eventService->createEvent($this->group, $this->creator, $eventData);

        $this->assertInstanceOf(GroupEvent::class, $event);
        $this->assertEquals('Test Event', $event->name);
        $this->assertEquals($this->group->id, $event->group_id);
        $this->assertEquals($this->creator->id, $event->created_by_user_id);
        $this->assertEquals('upcoming', $event->status);
        $this->assertEquals(100, $event->attendance_bonus); // default
        $this->assertEquals(2, $event->auto_prompt_hours_after); // default
    }

    public function test_creates_event_with_all_fields(): void
    {
        $eventDate = Carbon::now()->addDays(7);
        $rsvpDeadline = Carbon::now()->addDays(5);

        $eventData = [
            'name' => 'Full Event',
            'description' => 'A complete event with all fields',
            'event_date' => $eventDate,
            'location' => 'Central Park',
            'attendance_bonus' => 250,
            'rsvp_deadline' => $rsvpDeadline,
            'auto_prompt_hours_after' => 4,
        ];

        $event = $this->eventService->createEvent($this->group, $this->creator, $eventData);

        $this->assertEquals('Full Event', $event->name);
        $this->assertEquals('A complete event with all fields', $event->description);
        $this->assertEquals('Central Park', $event->location);
        $this->assertEquals(250, $event->attendance_bonus);
        $this->assertEquals($rsvpDeadline->toDateTimeString(), $event->rsvp_deadline->toDateTimeString());
        $this->assertEquals(4, $event->auto_prompt_hours_after);
    }

    public function test_records_rsvp_creates_new_entry(): void
    {
        $event = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
            'status' => 'upcoming',
        ]);

        $user = User::factory()->create();
        $this->group->users()->attach($user->id, [
            'id' => Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $result = $this->eventService->recordRsvp($event, $user, 'going');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('rsvp', $result);
        $this->assertArrayHasKey('previous_response', $result);
        $this->assertArrayHasKey('was_changed', $result);
        $this->assertInstanceOf(GroupEventRsvp::class, $result['rsvp']);
        $this->assertEquals($event->id, $result['rsvp']->event_id);
        $this->assertEquals($user->id, $result['rsvp']->user_id);
        $this->assertEquals('going', $result['rsvp']->response);
        $this->assertNull($result['previous_response']);
        $this->assertFalse($result['was_changed']);
    }

    public function test_records_rsvp_updates_existing_entry(): void
    {
        $event = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
            'status' => 'upcoming',
        ]);

        $user = User::factory()->create();

        // First RSVP
        $this->eventService->recordRsvp($event, $user, 'maybe');

        // Update RSVP
        $result = $this->eventService->recordRsvp($event, $user, 'going');

        $this->assertEquals(1, GroupEventRsvp::where('event_id', $event->id)->count());
        $this->assertEquals('going', $result['rsvp']->response);
        $this->assertEquals('maybe', $result['previous_response']);
        $this->assertTrue($result['was_changed']);
    }

    public function test_get_rsvp_counts_returns_correct_counts(): void
    {
        $event = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
        ]);

        // Add 5 users to group
        $users = User::factory()->count(5)->create();
        foreach ($users as $user) {
            $this->group->users()->attach($user->id, [
                'id' => Str::uuid(),
                'points' => 1000,
                'role' => 'participant',
            ]);
        }

        // Record RSVPs
        $this->eventService->recordRsvp($event, $users[0], 'going');
        $this->eventService->recordRsvp($event, $users[1], 'going');
        $this->eventService->recordRsvp($event, $users[2], 'maybe');
        $this->eventService->recordRsvp($event, $users[3], 'not_going');
        // users[4] and creator don't RSVP

        $counts = $this->eventService->getRsvpCounts($event);

        $this->assertEquals(2, $counts['going']);
        $this->assertEquals(1, $counts['maybe']);
        $this->assertEquals(1, $counts['not_going']);
        $this->assertEquals(2, $counts['no_response']); // users[4] + creator
    }

    public function test_record_attendance_creates_entries_for_all_group_users(): void
    {
        $event = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
            'status' => 'upcoming',
            'attendance_bonus' => 150,
        ]);

        // Add users to group
        $attendees = User::factory()->count(3)->create();
        $absentees = User::factory()->count(2)->create();
        
        foreach (array_merge($attendees->all(), $absentees->all()) as $user) {
            $this->group->users()->attach($user->id, [
                'id' => Str::uuid(),
                'points' => 1000,
                'role' => 'participant',
            ]);
        }

        // Expect point service to be called for each attendee
        $this->pointService->expects($this->exactly(3))
            ->method('awardPoints');

        // Record attendance
        $attendeeIds = $attendees->pluck('id')->toArray();
        $this->eventService->recordAttendance($event, $this->creator, $attendeeIds);

        // Verify attendance records
        $attendanceRecords = GroupEventAttendance::where('event_id', $event->id)->get();
        $this->assertCount(6, $attendanceRecords); // 3 attendees + 2 absentees + 1 creator

        // Check attendees
        foreach ($attendees as $attendee) {
            $record = $attendanceRecords->where('user_id', $attendee->id)->first();
            $this->assertTrue($record->attended);
            $this->assertEquals($this->creator->id, $record->reported_by_user_id);
        }

        // Check absentees
        foreach ($absentees as $absentee) {
            $record = $attendanceRecords->where('user_id', $absentee->id)->first();
            $this->assertFalse($record->attended);
        }

        // Check creator (not in attendee list)
        $creatorRecord = $attendanceRecords->where('user_id', $this->creator->id)->first();
        $this->assertFalse($creatorRecord->attended);

        // Verify event status updated
        $event->refresh();
        $this->assertEquals('completed', $event->status);
    }

    public function test_record_attendance_throws_exception_if_already_recorded(): void
    {
        $event = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
        ]);

        // Create existing attendance record
        GroupEventAttendance::create([
            'event_id' => $event->id,
            'user_id' => $this->creator->id,
            'attended' => true,
            'reported_by_user_id' => $this->creator->id,
            'reported_at' => now(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Attendance already recorded for this event');

        $this->eventService->recordAttendance($event, $this->creator, [$this->creator->id]);
    }

    public function test_get_events_for_group_categorizes_correctly(): void
    {
        // Create various events
        $upcomingEvent = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
            'status' => 'upcoming',
            'event_date' => Carbon::now()->addDays(2),
        ]);

        $pastUnprocessedEvent = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
            'status' => 'upcoming',
            'event_date' => Carbon::now()->subDays(1),
        ]);

        $completedEvent = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
            'status' => 'completed',
            'event_date' => Carbon::now()->subDays(3),
        ]);

        $result = $this->eventService->getEventsForGroup($this->group);

        $this->assertCount(1, $result['upcoming']);
        $this->assertEquals($upcomingEvent->id, $result['upcoming'][0]->id);

        $this->assertCount(1, $result['past_unprocessed']);
        $this->assertEquals($pastUnprocessedEvent->id, $result['past_unprocessed'][0]->id);

        $this->assertCount(1, $result['past_processed']);
        $this->assertEquals($completedEvent->id, $result['past_processed'][0]->id);
    }

    public function test_get_events_pending_attendance_prompt_filters_correctly(): void
    {
        // Event that needs prompt (2 hours after event time)
        $needsPromptEvent = GroupEvent::factory()->create([
            'status' => 'upcoming',
            'event_date' => Carbon::now()->subHours(3),
            'auto_prompt_hours_after' => 2,
        ]);

        // Event too recent
        $tooRecentEvent = GroupEvent::factory()->create([
            'status' => 'upcoming',
            'event_date' => Carbon::now()->subHours(1),
            'auto_prompt_hours_after' => 2,
        ]);

        // Completed event
        $completedEvent = GroupEvent::factory()->create([
            'status' => 'completed',
            'event_date' => Carbon::now()->subHours(3),
            'auto_prompt_hours_after' => 2,
        ]);

        $events = $this->eventService->getEventsPendingAttendancePrompt();

        $this->assertCount(1, $events);
        $this->assertEquals($needsPromptEvent->id, $events[0]->id);
    }

    public function test_mark_event_as_expired_updates_status(): void
    {
        $event = GroupEvent::factory()->create([
            'status' => 'upcoming',
        ]);

        $this->eventService->markEventAsExpired($event);

        $event->refresh();
        $this->assertEquals('expired', $event->status);
    }

    public function test_attendance_bonus_updates_last_wager_joined_for_decay(): void
    {
        $event = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
            'status' => 'upcoming',
            'attendance_bonus' => 200,
        ]);

        $user = User::factory()->create();
        $this->group->users()->attach($user->id, [
            'id' => Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
            'last_wager_joined_at' => Carbon::now()->subDays(10),
        ]);

        // Mock point service
        $this->pointService->expects($this->once())
            ->method('awardPoints')
            ->with(
                $this->callback(function ($arg) use ($user) {
                    return $arg->id === $user->id;
                }),
                $this->callback(function ($arg) use ($event) {
                    return $arg->id === $event->group_id;
                }),
                200,
                'event_attendance_bonus',
                null,
                null
            );

        // Record attendance
        $this->eventService->recordAttendance($event, $this->creator, [$user->id]);

        // Verify last_wager_joined_at was updated
        $userGroup = $this->group->users()->where('user_id', $user->id)->first();
        $this->assertNotNull($userGroup->pivot->last_wager_joined_at);
        $this->assertTrue(
            Carbon::parse($userGroup->pivot->last_wager_joined_at)->isToday()
        );

        // Verify bonus was marked as awarded
        $attendance = GroupEventAttendance::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();
        $this->assertTrue($attendance->bonus_awarded);
    }
}