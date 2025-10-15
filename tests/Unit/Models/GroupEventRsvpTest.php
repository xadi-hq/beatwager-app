<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\GroupEvent;
use App\Models\GroupEventRsvp;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupEventRsvpTest extends TestCase
{
    use RefreshDatabase;

    private GroupEvent $event;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = GroupEvent::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_creates_rsvp_with_valid_response_types(): void
    {
        $validResponses = ['going', 'maybe', 'not_going'];

        foreach ($validResponses as $response) {
            $rsvp = GroupEventRsvp::create([
                'event_id' => $this->event->id,
                'user_id' => User::factory()->create()->id,
                'response' => $response,
            ]);

            $this->assertDatabaseHas('group_event_rsvps', [
                'id' => $rsvp->id,
                'response' => $response,
            ]);
        }
    }

    public function test_relationships_are_properly_defined(): void
    {
        $rsvp = GroupEventRsvp::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'response' => 'going',
        ]);

        // Test event relationship
        $this->assertInstanceOf(GroupEvent::class, $rsvp->event);
        $this->assertEquals($this->event->id, $rsvp->event->id);

        // Test user relationship
        $this->assertInstanceOf(User::class, $rsvp->user);
        $this->assertEquals($this->user->id, $rsvp->user->id);
    }

    public function test_unique_constraint_prevents_duplicate_rsvps(): void
    {
        // Create first RSVP
        GroupEventRsvp::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'response' => 'going',
        ]);

        // Attempt to create duplicate should fail
        $this->expectException(QueryException::class);
        
        GroupEventRsvp::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'response' => 'maybe',
        ]);
    }

    public function test_multiple_users_can_rsvp_to_same_event(): void
    {
        $users = User::factory()->count(3)->create();

        foreach ($users as $index => $user) {
            $responses = ['going', 'maybe', 'not_going'];
            $rsvp = GroupEventRsvp::create([
                'event_id' => $this->event->id,
                'user_id' => $user->id,
                'response' => $responses[$index],
            ]);

            $this->assertNotNull($rsvp->id);
        }

        $this->assertEquals(3, GroupEventRsvp::where('event_id', $this->event->id)->count());
    }

    public function test_same_user_can_rsvp_to_multiple_events(): void
    {
        $events = GroupEvent::factory()->count(3)->create();

        foreach ($events as $event) {
            $rsvp = GroupEventRsvp::create([
                'event_id' => $event->id,
                'user_id' => $this->user->id,
                'response' => 'going',
            ]);

            $this->assertNotNull($rsvp->id);
        }

        $this->assertEquals(3, GroupEventRsvp::where('user_id', $this->user->id)->count());
    }

    public function test_cascading_delete_when_event_is_deleted(): void
    {
        // Create RSVP
        $rsvp = GroupEventRsvp::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
        ]);

        $rsvpId = $rsvp->id;

        // Delete the event
        $this->event->delete();

        // RSVP should be deleted due to cascade
        $this->assertDatabaseMissing('group_event_rsvps', [
            'id' => $rsvpId,
        ]);
    }

    public function test_cascading_delete_when_user_is_deleted(): void
    {
        // Create RSVP
        $rsvp = GroupEventRsvp::factory()->create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
        ]);

        $rsvpId = $rsvp->id;

        // Delete the user
        $this->user->delete();

        // RSVP should be deleted due to cascade
        $this->assertDatabaseMissing('group_event_rsvps', [
            'id' => $rsvpId,
        ]);
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $rsvp = new GroupEventRsvp([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'response' => 'maybe',
        ]);

        $this->assertEquals($this->event->id, $rsvp->event_id);
        $this->assertEquals($this->user->id, $rsvp->user_id);
        $this->assertEquals('maybe', $rsvp->response);
    }

    public function test_update_existing_rsvp_response(): void
    {
        $rsvp = GroupEventRsvp::create([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'response' => 'maybe',
        ]);

        // Update response
        $rsvp->update(['response' => 'going']);

        $this->assertEquals('going', $rsvp->fresh()->response);
        $this->assertDatabaseHas('group_event_rsvps', [
            'id' => $rsvp->id,
            'response' => 'going',
        ]);
    }
}