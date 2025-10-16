<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\GroupEventAttendance;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GroupEventAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private GroupEvent $event;
    private User $user;
    private User $reporter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = GroupEvent::factory()->create();
        $this->user = User::factory()->create();
        $this->reporter = User::factory()->create();
    }

    public function test_creates_attendance_record_with_valid_data(): void
    {
        $attendance = GroupEventAttendance::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'attended' => true,
            'reported_by_user_id' => $this->reporter->id,
            'reported_at' => now(),
            'bonus_awarded' => false,
        ]);

        $this->assertNotNull($attendance->id);
        $this->assertTrue($attendance->attended);
        $this->assertFalse($attendance->bonus_awarded);
        $this->assertEquals($this->reporter->id, $attendance->reported_by_user_id);
    }

    public function test_relationships_are_properly_defined(): void
    {
        $attendance = GroupEventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'reported_by_user_id' => $this->reporter->id,
        ]);

        // Test event relationship
        $this->assertInstanceOf(GroupEvent::class, $attendance->event);
        $this->assertEquals($this->event->id, $attendance->event->id);

        // Test user relationship
        $this->assertInstanceOf(User::class, $attendance->user);
        $this->assertEquals($this->user->id, $attendance->user->id);

        // Test reportedBy relationship
        $this->assertInstanceOf(User::class, $attendance->reportedBy);
        $this->assertEquals($this->reporter->id, $attendance->reportedBy->id);
    }

    public function test_boolean_casts_work_correctly(): void
    {
        // Test with true values
        $attendance1 = GroupEventAttendance::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'attended' => 1,
            'reported_by_user_id' => $this->reporter->id,
            'reported_at' => now(),
            'bonus_awarded' => 1,
        ]);

        $this->assertIsBool($attendance1->attended);
        $this->assertTrue($attendance1->attended);
        $this->assertIsBool($attendance1->bonus_awarded);
        $this->assertTrue($attendance1->bonus_awarded);

        // Test with false values
        $attendance2 = GroupEventAttendance::create([
            'event_id' => $this->event->id,
            'user_id' => User::factory()->create()->id,
            'attended' => 0,
            'reported_by_user_id' => $this->reporter->id,
            'reported_at' => now(),
            'bonus_awarded' => 0,
        ]);

        $this->assertIsBool($attendance2->attended);
        $this->assertFalse($attendance2->attended);
        $this->assertIsBool($attendance2->bonus_awarded);
        $this->assertFalse($attendance2->bonus_awarded);
    }

    public function test_datetime_cast_for_reported_at(): void
    {
        $reportedAt = Carbon::parse('2025-10-15 14:30:00');
        
        $attendance = GroupEventAttendance::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'attended' => true,
            'reported_by_user_id' => $this->reporter->id,
            'reported_at' => $reportedAt,
        ]);

        $this->assertInstanceOf(Carbon::class, $attendance->reported_at);
        $this->assertEquals($reportedAt->toDateTimeString(), $attendance->reported_at->toDateTimeString());
    }

    public function test_unique_constraint_prevents_duplicate_attendance(): void
    {
        // Create first attendance record
        GroupEventAttendance::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'attended' => true,
            'reported_by_user_id' => $this->reporter->id,
            'reported_at' => now(),
        ]);

        // Attempt to create duplicate should fail
        $this->expectException(QueryException::class);
        
        GroupEventAttendance::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'attended' => false,
            'reported_by_user_id' => $this->reporter->id,
            'reported_at' => now(),
        ]);
    }

    public function test_multiple_users_can_have_attendance_for_same_event(): void
    {
        $users = User::factory()->count(5)->create();
        $attendedCount = 0;

        foreach ($users as $index => $user) {
            $attended = $index < 3; // First 3 attended, last 2 didn't
            if ($attended) $attendedCount++;

            GroupEventAttendance::create([
                'event_id' => $this->event->id,
                'user_id' => $user->id,
                'attended' => $attended,
                'reported_by_user_id' => $this->reporter->id,
                'reported_at' => now(),
                'bonus_awarded' => $attended,
            ]);
        }

        $attendanceRecords = GroupEventAttendance::where('event_id', $this->event->id)->get();
        $this->assertCount(5, $attendanceRecords);
        $this->assertEquals($attendedCount, $attendanceRecords->where('attended', true)->count());
        $this->assertEquals($attendedCount, $attendanceRecords->where('bonus_awarded', true)->count());
    }

    public function test_same_user_can_have_attendance_for_multiple_events(): void
    {
        $events = GroupEvent::factory()->count(3)->create();

        foreach ($events as $index => $event) {
            GroupEventAttendance::create([
                'event_id' => $event->id,
                'user_id' => $this->user->id,
                'attended' => $index !== 1, // Didn't attend second event
                'reported_by_user_id' => $this->reporter->id,
                'reported_at' => now()->addMinutes($index * 10),
            ]);
        }

        $userAttendance = GroupEventAttendance::where('user_id', $this->user->id)->get();
        $this->assertCount(3, $userAttendance);
        $this->assertEquals(2, $userAttendance->where('attended', true)->count());
    }

    public function test_cascading_delete_when_event_is_deleted(): void
    {
        $attendance = GroupEventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
        ]);

        $attendanceId = $attendance->id;

        // Delete the event
        $this->event->delete();

        // Attendance should be deleted due to cascade
        $this->assertDatabaseMissing('group_event_attendance', [
            'id' => $attendanceId,
        ]);
    }

    public function test_cascading_delete_when_user_is_deleted(): void
    {
        $attendance = GroupEventAttendance::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'reported_by_user_id' => $this->reporter->id,
        ]);

        $attendanceId = $attendance->id;

        // Delete the user
        $this->user->delete();

        // Attendance should be deleted due to cascade
        $this->assertDatabaseMissing('group_event_attendance', [
            'id' => $attendanceId,
        ]);
    }

    public function test_bonus_can_be_updated_after_creation(): void
    {
        $attendance = GroupEventAttendance::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'attended' => true,
            'reported_by_user_id' => $this->reporter->id,
            'reported_at' => now(),
            'bonus_awarded' => false,
        ]);

        $this->assertFalse($attendance->bonus_awarded);

        // Update bonus_awarded
        $attendance->update(['bonus_awarded' => true]);

        $this->assertTrue($attendance->fresh()->bonus_awarded);
    }

    public function test_attendance_tracking_for_group_statistics(): void
    {
        $group = Group::factory()->create();
        $event = GroupEvent::factory()->create(['group_id' => $group->id]);
        $users = User::factory()->count(10)->create();

        // Create attendance records: 7 attended, 3 didn't
        foreach ($users as $index => $user) {
            GroupEventAttendance::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'attended' => $index < 7,
                'reported_by_user_id' => $this->reporter->id,
                'reported_at' => now(),
                'bonus_awarded' => $index < 7,
            ]);
        }

        // Query statistics
        $stats = GroupEventAttendance::where('event_id', $event->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN attended THEN 1 ELSE 0 END) as attended_count')
            ->selectRaw('SUM(CASE WHEN bonus_awarded THEN 1 ELSE 0 END) as bonuses_awarded')
            ->first();

        $this->assertEquals(10, $stats->total);
        $this->assertEquals(7, $stats->attended_count);
        $this->assertEquals(7, $stats->bonuses_awarded);
    }
}