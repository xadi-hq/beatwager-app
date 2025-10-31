<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Group;
use App\Models\ScheduledMessage;
use App\Services\ScheduledMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ScheduledMessageServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScheduledMessageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScheduledMessageService();
    }

    /** @test */
    public function it_creates_scheduled_message_with_required_fields()
    {
        $group = Group::factory()->create();

        $data = [
            'title' => 'Team Meeting',
            'scheduled_date' => now()->addDays(7),
        ];

        $message = $this->service->create($group, $data);

        $this->assertInstanceOf(ScheduledMessage::class, $message);
        $this->assertEquals('Team Meeting', $message->title);
        $this->assertEquals($group->id, $message->group_id);
        $this->assertEquals('custom', $message->message_type);
        $this->assertTrue($message->is_active);
        $this->assertFalse($message->is_recurring);
    }

    /** @test */
    public function it_creates_scheduled_message_with_all_fields()
    {
        $group = Group::factory()->create();

        $data = [
            'title' => 'Birthday: John',
            'message_type' => 'birthday',
            'scheduled_date' => now()->addDays(30),
            'message_template' => 'Happy birthday to {name}!',
            'llm_instructions' => 'Make it extra festive',
            'is_recurring' => true,
            'recurrence_type' => 'yearly',
            'is_drop_event' => true,
            'drop_amount' => 100,
        ];

        $message = $this->service->create($group, $data);

        $this->assertEquals('birthday', $message->message_type);
        $this->assertEquals('Happy birthday to {name}!', $message->message_template);
        $this->assertEquals('Make it extra festive', $message->llm_instructions);
        $this->assertTrue($message->is_recurring);
        $this->assertEquals('yearly', $message->recurrence_type);
        $this->assertTrue($message->is_drop_event);
        $this->assertEquals(100, $message->drop_amount);
    }

    /** @test */
    public function it_updates_scheduled_message()
    {
        $group = Group::factory()->create();
        $message = ScheduledMessage::factory()->create([
            'group_id' => $group->id,
            'title' => 'Old Title',
            'scheduled_date' => now()->addDays(7),
        ]);

        $updated = $this->service->update($message, [
            'title' => 'New Title',
            'scheduled_date' => now()->addDays(14),
        ]);

        $this->assertEquals('New Title', $updated->title);
        $this->assertTrue($updated->scheduled_date->isFuture());
    }

    /** @test */
    public function it_toggles_active_status()
    {
        $message = ScheduledMessage::factory()->create(['is_active' => true]);

        $toggled = $this->service->toggleActive($message);
        $this->assertFalse($toggled->is_active);

        $toggledAgain = $this->service->toggleActive($toggled);
        $this->assertTrue($toggledAgain->is_active);
    }

    /** @test */
    public function it_deletes_scheduled_message()
    {
        $message = ScheduledMessage::factory()->create();
        $messageId = $message->id;

        $this->service->delete($message);

        $this->assertDatabaseMissing('scheduled_messages', ['id' => $messageId]);
    }

    /** @test */
    public function it_returns_messages_for_group()
    {
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        ScheduledMessage::factory()->count(3)->create(['group_id' => $group1->id]);
        ScheduledMessage::factory()->count(2)->create(['group_id' => $group2->id]);

        $messages = $this->service->getForGroup($group1);

        $this->assertCount(3, $messages);
        $this->assertTrue($messages->every(fn($m) => $m->group_id === $group1->id));
    }

    /** @test */
    public function it_filters_upcoming_messages_only()
    {
        $group = Group::factory()->create();

        ScheduledMessage::factory()->create([
            'group_id' => $group->id,
            'scheduled_date' => now()->subDays(7), // Past
            'is_recurring' => false,
        ]);

        ScheduledMessage::factory()->create([
            'group_id' => $group->id,
            'scheduled_date' => now()->addDays(7), // Future
            'is_recurring' => false,
        ]);

        ScheduledMessage::factory()->create([
            'group_id' => $group->id,
            'scheduled_date' => now()->subDays(30), // Past but recurring
            'is_recurring' => true,
            'recurrence_type' => 'monthly',
        ]);

        $messages = $this->service->getForGroup($group, ['upcoming_only' => true]);

        // Should include: future one-time + recurring
        $this->assertCount(2, $messages);
    }

    /** @test */
    public function it_filters_by_active_status()
    {
        $group = Group::factory()->create();

        ScheduledMessage::factory()->count(2)->create([
            'group_id' => $group->id,
            'is_active' => true,
        ]);

        ScheduledMessage::factory()->count(3)->create([
            'group_id' => $group->id,
            'is_active' => false,
        ]);

        $activeMessages = $this->service->getForGroup($group, ['is_active' => true]);
        $inactiveMessages = $this->service->getForGroup($group, ['is_active' => false]);

        $this->assertCount(2, $activeMessages);
        $this->assertCount(3, $inactiveMessages);
    }

    /** @test */
    public function it_identifies_one_time_message_to_send_today()
    {
        $today = Carbon::today();

        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => $today,
            'is_recurring' => false,
            'is_active' => true,
            'last_sent_at' => null,
        ]);

        $this->assertTrue($message->shouldSendToday());
    }

    /** @test */
    public function it_does_not_resend_one_time_message()
    {
        $today = Carbon::today();

        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => $today,
            'is_recurring' => false,
            'is_active' => true,
            'last_sent_at' => now()->subHours(1), // Already sent
        ]);

        $this->assertFalse($message->shouldSendToday());
    }

    /** @test */
    public function it_identifies_daily_recurring_message()
    {
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => now()->subDays(10), // Started 10 days ago
            'is_recurring' => true,
            'recurrence_type' => 'daily',
            'is_active' => true,
            'last_sent_at' => now()->subDays(1), // Last sent yesterday
        ]);

        $this->assertTrue($message->shouldSendToday());
    }

    /** @test */
    public function it_identifies_weekly_recurring_message()
    {
        // Create message scheduled for same day of week
        $scheduledDate = Carbon::today()->subWeeks(2); // 2 weeks ago, same weekday
        
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => $scheduledDate,
            'is_recurring' => true,
            'recurrence_type' => 'weekly',
            'is_active' => true,
            'last_sent_at' => now()->subWeeks(1), // Last sent a week ago
        ]);

        $this->assertTrue($message->shouldSendToday());
    }

    /** @test */
    public function it_does_not_send_weekly_message_on_wrong_day()
    {
        // Create message scheduled for different day of week
        $scheduledDate = Carbon::today()->subWeeks(1)->addDays(1); // Different weekday
        
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => $scheduledDate,
            'is_recurring' => true,
            'recurrence_type' => 'weekly',
            'is_active' => true,
            'last_sent_at' => null,
        ]);

        $this->assertFalse($message->shouldSendToday());
    }

    /** @test */
    public function it_identifies_monthly_recurring_message()
    {
        $today = Carbon::today();
        $scheduledDate = $today->copy()->subMonths(2); // 2 months ago, same day
        
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => $scheduledDate,
            'is_recurring' => true,
            'recurrence_type' => 'monthly',
            'is_active' => true,
            'last_sent_at' => now()->subMonths(1), // Last sent a month ago
        ]);

        $this->assertTrue($message->shouldSendToday());
    }

    /** @test */
    public function it_identifies_yearly_recurring_message()
    {
        $today = Carbon::today();
        $scheduledDate = $today->copy()->subYears(1); // 1 year ago, same date
        
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => $scheduledDate,
            'is_recurring' => true,
            'recurrence_type' => 'yearly',
            'is_active' => true,
            'last_sent_at' => now()->subYears(1), // Last sent a year ago
        ]);

        $this->assertTrue($message->shouldSendToday());
    }

    /** @test */
    public function it_does_not_send_recurring_message_already_sent_today()
    {
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => now()->subWeeks(1),
            'is_recurring' => true,
            'recurrence_type' => 'daily',
            'is_active' => true,
            'last_sent_at' => now()->subHours(2), // Already sent today
        ]);

        $this->assertFalse($message->shouldSendToday());
    }

    /** @test */
    public function it_does_not_send_inactive_messages()
    {
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => today(),
            'is_recurring' => false,
            'is_active' => false, // Inactive
            'last_sent_at' => null,
        ]);

        $this->assertFalse($message->shouldSendToday());
    }

    /** @test */
    public function it_gets_all_messages_to_send_today()
    {
        // Create various messages
        $todayOneTime = ScheduledMessage::factory()->create([
            'scheduled_date' => today(),
            'is_recurring' => false,
            'is_active' => true,
            'last_sent_at' => null,
        ]);

        $dailyRecurring = ScheduledMessage::factory()->create([
            'scheduled_date' => now()->subDays(10),
            'is_recurring' => true,
            'recurrence_type' => 'daily',
            'is_active' => true,
            'last_sent_at' => now()->subDays(1),
        ]);

        // Should not be included
        ScheduledMessage::factory()->create([
            'scheduled_date' => now()->addDays(7), // Future
            'is_recurring' => false,
            'is_active' => true,
            'last_sent_at' => null,
        ]);

        ScheduledMessage::factory()->create([
            'scheduled_date' => today(),
            'is_recurring' => false,
            'is_active' => false, // Inactive
            'last_sent_at' => null,
        ]);

        $messages = $this->service->getMessagesToSendToday();

        $this->assertCount(2, $messages);
        $this->assertTrue($messages->contains($todayOneTime));
        $this->assertTrue($messages->contains($dailyRecurring));
    }

    /** @test */
    public function it_marks_message_as_sent()
    {
        $message = ScheduledMessage::factory()->create([
            'last_sent_at' => null,
        ]);

        $this->assertNull($message->last_sent_at);

        $this->service->markAsSent($message);

        $message->refresh();
        $this->assertNotNull($message->last_sent_at);
        $this->assertTrue($message->last_sent_at->isToday());
    }

    /** @test */
    public function it_handles_drop_events()
    {
        $group = Group::factory()->create();
        
        // Create 3 users in the group
        $users = \App\Models\User::factory()->count(3)->create();
        foreach ($users as $user) {
            $group->users()->attach($user->id, [
                'id' => \Illuminate\Support\Str::uuid(),
                'points' => 500,
                'role' => 'participant',
            ]);
        }

        $message = ScheduledMessage::factory()->create([
            'group_id' => $group->id,
            'is_drop_event' => true,
            'drop_amount' => 100,
        ]);

        $recipientsCount = $message->distributeDropToGroup();

        $this->assertEquals(3, $recipientsCount);

        // Verify each user received points
        foreach ($users as $user) {
            $balance = $user->groups()->where('group_id', $group->id)->first()->pivot->points;
            $this->assertEquals(600, $balance); // 500 + 100
        }
    }

    /** @test */
    public function it_does_not_distribute_drops_when_not_drop_event()
    {
        $group = Group::factory()->create();
        $user = \App\Models\User::factory()->create();
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 500,
            'role' => 'participant',
        ]);

        $message = ScheduledMessage::factory()->create([
            'group_id' => $group->id,
            'is_drop_event' => false, // Not a drop
            'drop_amount' => 100,
        ]);

        $recipientsCount = $message->distributeDropToGroup();

        $this->assertEquals(0, $recipientsCount);
        
        // Verify points unchanged
        $balance = $user->groups()->where('group_id', $group->id)->first()->pivot->points;
        $this->assertEquals(500, $balance);
    }

    /** @test */
    public function it_calculates_next_occurrence_for_one_time_message()
    {
        $futureDate = now()->addDays(7);
        
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => $futureDate,
            'is_recurring' => false,
            'is_active' => true,
        ]);

        $next = $message->getNextOccurrence();

        $this->assertNotNull($next);
        $this->assertEquals($futureDate->toDateString(), $next->toDateString());
    }

    /** @test */
    public function it_returns_null_for_past_one_time_message()
    {
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => now()->subDays(7), // Past
            'is_recurring' => false,
            'is_active' => true,
        ]);

        $next = $message->getNextOccurrence();

        $this->assertNull($next);
    }

    /** @test */
    public function it_calculates_next_occurrence_for_daily_recurring()
    {
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => now()->subDays(10),
            'is_recurring' => true,
            'recurrence_type' => 'daily',
            'is_active' => true,
        ]);

        $next = $message->getNextOccurrence();

        $this->assertNotNull($next);
        $this->assertTrue($next->isNextDay());
    }

    /** @test */
    public function it_calculates_next_occurrence_for_weekly_recurring()
    {
        $scheduledDate = now()->subWeeks(2); // 2 weeks ago
        
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => $scheduledDate,
            'is_recurring' => true,
            'recurrence_type' => 'weekly',
            'is_active' => true,
        ]);

        $next = $message->getNextOccurrence();

        $this->assertNotNull($next);
        $this->assertEquals($scheduledDate->dayOfWeek, $next->dayOfWeek);
        $this->assertTrue($next->isFuture());
    }

    /** @test */
    public function it_returns_null_for_inactive_message_next_occurrence()
    {
        $message = ScheduledMessage::factory()->create([
            'scheduled_date' => now()->addDays(7),
            'is_recurring' => true,
            'recurrence_type' => 'daily',
            'is_active' => false, // Inactive
        ]);

        $next = $message->getNextOccurrence();

        $this->assertNull($next);
    }
}
