<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\GroupEventAttendance;
use App\Models\GroupEventRsvp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GroupEventTest extends TestCase
{
    use RefreshDatabase;

    private GroupEvent $event;
    private Group $group;
    private User $creator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->group = Group::factory()->create();
        $this->creator = User::factory()->create();
        
        $this->event = GroupEvent::factory()->create([
            'group_id' => $this->group->id,
            'created_by_user_id' => $this->creator->id,
            'event_date' => Carbon::now()->addDays(3),
            'status' => 'upcoming',
        ]);
    }

    public function test_relationships_are_properly_defined(): void
    {
        // Test group relationship
        $this->assertInstanceOf(Group::class, $this->event->group);
        $this->assertEquals($this->group->id, $this->event->group->id);

        // Test creator relationship
        $this->assertInstanceOf(User::class, $this->event->creator);
        $this->assertEquals($this->creator->id, $this->event->creator->id);

        // Test rsvps relationship
        $rsvp = GroupEventRsvp::factory()->create(['event_id' => $this->event->id]);
        $this->assertCount(1, $this->event->rsvps);
        $this->assertEquals($rsvp->id, $this->event->rsvps->first()->id);

        // Test attendance relationship
        $attendance = GroupEventAttendance::factory()->create(['event_id' => $this->event->id]);
        $this->assertCount(1, $this->event->attendance);
        $this->assertEquals($attendance->id, $this->event->attendance->first()->id);
    }

    public function test_is_upcoming_returns_true_for_future_upcoming_events(): void
    {
        $this->assertTrue($this->event->isUpcoming());
    }

    public function test_is_upcoming_returns_false_for_past_events(): void
    {
        $pastEvent = GroupEvent::factory()->create([
            'event_date' => Carbon::now()->subDays(1),
            'status' => 'upcoming',
        ]);

        $this->assertFalse($pastEvent->isUpcoming());
    }

    public function test_is_upcoming_returns_false_for_non_upcoming_status(): void
    {
        $completedEvent = GroupEvent::factory()->create([
            'event_date' => Carbon::now()->addDays(1),
            'status' => 'completed',
        ]);

        $this->assertFalse($completedEvent->isUpcoming());
    }

    public function test_is_past_correctly_identifies_past_events(): void
    {
        $futureEvent = GroupEvent::factory()->create([
            'event_date' => Carbon::now()->addDays(1),
        ]);
        $pastEvent = GroupEvent::factory()->create([
            'event_date' => Carbon::now()->subDays(1),
        ]);

        $this->assertFalse($futureEvent->isPast());
        $this->assertTrue($pastEvent->isPast());
    }

    public function test_is_processed_checks_completed_status(): void
    {
        $upcomingEvent = GroupEvent::factory()->create(['status' => 'upcoming']);
        $completedEvent = GroupEvent::factory()->create(['status' => 'completed']);
        $expiredEvent = GroupEvent::factory()->create(['status' => 'expired']);
        $cancelledEvent = GroupEvent::factory()->create(['status' => 'cancelled']);

        $this->assertFalse($upcomingEvent->isProcessed());
        $this->assertTrue($completedEvent->isProcessed());
        $this->assertFalse($expiredEvent->isProcessed());
        $this->assertFalse($cancelledEvent->isProcessed());
    }

    public function test_needs_attendance_prompt_returns_true_when_conditions_met(): void
    {
        // Event is 3 hours past, auto_prompt is 2 hours, no attendance recorded
        $event = GroupEvent::factory()->create([
            'status' => 'upcoming',
            'event_date' => Carbon::now()->subHours(3),
            'auto_prompt_hours_after' => 2,
        ]);

        $this->assertTrue($event->needsAttendancePrompt());
    }

    public function test_needs_attendance_prompt_returns_false_when_too_early(): void
    {
        // Event is 1 hour past, auto_prompt is 2 hours
        $event = GroupEvent::factory()->create([
            'status' => 'upcoming',
            'event_date' => Carbon::now()->subHours(1),
            'auto_prompt_hours_after' => 2,
        ]);

        $this->assertFalse($event->needsAttendancePrompt());
    }

    public function test_needs_attendance_prompt_returns_false_for_non_upcoming_status(): void
    {
        $event = GroupEvent::factory()->create([
            'status' => 'completed',
            'event_date' => Carbon::now()->subHours(3),
            'auto_prompt_hours_after' => 2,
        ]);

        $this->assertFalse($event->needsAttendancePrompt());
    }

    public function test_needs_attendance_prompt_returns_false_when_attendance_exists(): void
    {
        $event = GroupEvent::factory()->create([
            'status' => 'upcoming',
            'event_date' => Carbon::now()->subHours(3),
            'auto_prompt_hours_after' => 2,
        ]);

        // Add attendance record
        GroupEventAttendance::factory()->create(['event_id' => $event->id]);

        $this->assertFalse($event->needsAttendancePrompt());
    }

    public function test_needs_attendance_prompt_edge_case_exactly_at_prompt_time(): void
    {
        // Mock the current time to be exactly at prompt time
        Carbon::setTestNow(Carbon::now());
        
        $event = GroupEvent::factory()->create([
            'status' => 'upcoming',
            'event_date' => Carbon::now()->subHours(2),
            'auto_prompt_hours_after' => 2,
        ]);

        $this->assertTrue($event->needsAttendancePrompt());
        
        Carbon::setTestNow(); // Reset
    }

    public function test_casts_are_properly_configured(): void
    {
        $event = GroupEvent::factory()->create([
            'event_date' => '2025-10-20 15:00:00',
            'rsvp_deadline' => '2025-10-19 12:00:00',
            'auto_prompt_hours_after' => '3',
            'attendance_bonus' => '150',
        ]);

        $this->assertInstanceOf(Carbon::class, $event->event_date);
        $this->assertInstanceOf(Carbon::class, $event->rsvp_deadline);
        $this->assertIsInt($event->auto_prompt_hours_after);
        $this->assertEquals(3, $event->auto_prompt_hours_after);
        $this->assertIsInt($event->attendance_bonus);
        $this->assertEquals(150, $event->attendance_bonus);
    }
}