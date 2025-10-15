<?php

declare(strict_types=1);

use App\Models\Challenge;
use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\User;
use App\Models\Wager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('expired wagers are filtered by active scope', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $group->users()->attach($creator, ['points' => 1000]);

    // Create expired wager (past deadline, no participants)
    $expiredWager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'betting_closes_at' => now()->subDays(1),
        'participants_count' => 0,
    ]);

    // Create active wager (past deadline, but has participants)
    $activeWager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'betting_closes_at' => now()->subDays(1),
        'participants_count' => 3,
    ]);

    // Create future wager (not expired yet)
    $futureWager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'betting_closes_at' => now()->addDays(1),
        'participants_count' => 0,
    ]);

    // Active scope should exclude expired wager
    $activeWagers = Wager::active()->get();
    expect($activeWagers)->toHaveCount(2);
    expect($activeWagers->pluck('id'))->toContain($activeWager->id, $futureWager->id);
    expect($activeWagers->pluck('id'))->not->toContain($expiredWager->id);

    // Expired scope should only return expired wager
    $expiredWagers = Wager::expired()->get();
    expect($expiredWagers)->toHaveCount(1);
    expect($expiredWagers->first()->id)->toBe($expiredWager->id);
});

test('expired challenges are filtered by active scope', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $group->users()->attach($creator, ['points' => 1000]);

    // Create expired challenge (past deadline, no acceptor)
    $expiredChallenge = Challenge::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'acceptance_deadline' => now()->subDays(1),
        'acceptor_id' => null,
    ]);

    // Create active challenge (past deadline, but has acceptor)
    $acceptor = User::factory()->create();
    $activeChallenge = Challenge::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'acceptance_deadline' => now()->subDays(1),
        'acceptor_id' => $acceptor->id,
    ]);

    // Create future challenge (not expired yet)
    $futureChallenge = Challenge::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'acceptance_deadline' => now()->addDays(1),
        'acceptor_id' => null,
    ]);

    // Active scope should exclude expired challenge
    $activeChallenges = Challenge::active()->get();
    expect($activeChallenges)->toHaveCount(2);
    expect($activeChallenges->pluck('id'))->toContain($activeChallenge->id, $futureChallenge->id);
    expect($activeChallenges->pluck('id'))->not->toContain($expiredChallenge->id);

    // Expired scope should only return expired challenge
    $expiredChallenges = Challenge::expired()->get();
    expect($expiredChallenges)->toHaveCount(1);
    expect($expiredChallenges->first()->id)->toBe($expiredChallenge->id);
});

test('expired events are filtered by active scope', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $rsvpUser = User::factory()->create();
    $group->users()->attach($creator, ['points' => 1000]);
    $group->users()->attach($rsvpUser, ['points' => 1000]);

    // Create expired event (past rsvp deadline, no RSVPs)
    $expiredEvent = GroupEvent::factory()->create([
        'group_id' => $group->id,
        'created_by_user_id' => $creator->id,
        'status' => 'upcoming',
        'event_date' => now()->addDays(5),
        'rsvp_deadline' => now()->subDays(1),
    ]);

    // Create active event (past rsvp deadline, but has RSVPs)
    $activeEvent = GroupEvent::factory()->create([
        'group_id' => $group->id,
        'created_by_user_id' => $creator->id,
        'status' => 'upcoming',
        'event_date' => now()->addDays(5),
        'rsvp_deadline' => now()->subDays(1),
    ]);
    $activeEvent->rsvps()->create([
        'user_id' => $rsvpUser->id,
        'response' => 'going',
    ]);

    // Create future event (rsvp deadline not passed yet)
    $futureEvent = GroupEvent::factory()->create([
        'group_id' => $group->id,
        'created_by_user_id' => $creator->id,
        'status' => 'upcoming',
        'event_date' => now()->addDays(5),
        'rsvp_deadline' => now()->addDays(1),
    ]);

    // Active scope should exclude expired event
    $activeEvents = GroupEvent::active()->get();
    expect($activeEvents)->toHaveCount(2);
    expect($activeEvents->pluck('id'))->toContain($activeEvent->id, $futureEvent->id);
    expect($activeEvents->pluck('id'))->not->toContain($expiredEvent->id);

    // Expired scope should only return expired event
    $expiredEvents = GroupEvent::expired()->get();
    expect($expiredEvents)->toHaveCount(1);
    expect($expiredEvents->first()->id)->toBe($expiredEvent->id);
});

test('cleanup command deletes expired wagers', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $group->users()->attach($creator, ['points' => 1000]);

    // Create expired wagers
    Wager::factory()->count(3)->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'betting_closes_at' => now()->subDays(1),
        'participants_count' => 0,
    ]);

    // Create active wager
    Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'betting_closes_at' => now()->addDays(1),
        'participants_count' => 0,
    ]);

    expect(Wager::count())->toBe(4);
    expect(Wager::expired()->count())->toBe(3);

    // Run cleanup command
    $this->artisan('cleanup:expired-items')
        ->assertSuccessful();

    // Expired wagers should be deleted
    expect(Wager::count())->toBe(1);
    expect(Wager::expired()->count())->toBe(0);
});

test('cleanup command deletes expired challenges', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $group->users()->attach($creator, ['points' => 1000]);

    // Create expired challenges
    Challenge::factory()->count(2)->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'acceptance_deadline' => now()->subDays(1),
        'acceptor_id' => null,
    ]);

    // Create active challenge
    Challenge::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'acceptance_deadline' => now()->addDays(1),
        'acceptor_id' => null,
    ]);

    expect(Challenge::count())->toBe(3);
    expect(Challenge::expired()->count())->toBe(2);

    // Run cleanup command
    $this->artisan('cleanup:expired-items')
        ->assertSuccessful();

    // Expired challenges should be deleted
    expect(Challenge::count())->toBe(1);
    expect(Challenge::expired()->count())->toBe(0);
});

test('cleanup command deletes expired events', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $group->users()->attach($creator, ['points' => 1000]);

    // Create expired events
    GroupEvent::factory()->count(2)->create([
        'group_id' => $group->id,
        'created_by_user_id' => $creator->id,
        'status' => 'upcoming',
        'event_date' => now()->subDays(1),
        'rsvp_deadline' => null,
    ]);

    // Create active event
    GroupEvent::factory()->create([
        'group_id' => $group->id,
        'created_by_user_id' => $creator->id,
        'status' => 'upcoming',
        'event_date' => now()->addDays(1),
        'rsvp_deadline' => null,
    ]);

    expect(GroupEvent::count())->toBe(3);
    expect(GroupEvent::expired()->count())->toBe(2);

    // Run cleanup command
    $this->artisan('cleanup:expired-items')
        ->assertSuccessful();

    // Expired events should be deleted
    expect(GroupEvent::count())->toBe(1);
    expect(GroupEvent::expired()->count())->toBe(0);
});

test('cleanup command dry run does not delete items', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $group->users()->attach($creator, ['points' => 1000]);

    // Create expired items
    Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'betting_closes_at' => now()->subDays(1),
        'participants_count' => 0,
    ]);

    Challenge::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'acceptance_deadline' => now()->subDays(1),
        'acceptor_id' => null,
    ]);

    GroupEvent::factory()->create([
        'group_id' => $group->id,
        'created_by_user_id' => $creator->id,
        'status' => 'upcoming',
        'event_date' => now()->subDays(1),
        'rsvp_deadline' => null,
    ]);

    $initialCount = Wager::count() + Challenge::count() + GroupEvent::count();

    // Run dry run
    $this->artisan('cleanup:expired-items --dry-run')
        ->assertSuccessful();

    // Nothing should be deleted
    $afterCount = Wager::count() + Challenge::count() + GroupEvent::count();
    expect($afterCount)->toBe($initialCount);
});

test('shouldBeDeleted helper methods work correctly', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();
    $group->users()->attach($creator, ['points' => 1000]);

    // Wager: should be deleted
    $expiredWager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'betting_closes_at' => now()->subDays(1),
        'participants_count' => 0,
    ]);
    expect($expiredWager->shouldBeDeleted())->toBeTrue();

    // Wager: should NOT be deleted (has participants)
    $activeWager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'betting_closes_at' => now()->subDays(1),
        'participants_count' => 3,
    ]);
    expect($activeWager->shouldBeDeleted())->toBeFalse();

    // Challenge: should be deleted
    $expiredChallenge = Challenge::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'acceptance_deadline' => now()->subDays(1),
        'acceptor_id' => null,
    ]);
    expect($expiredChallenge->shouldBeDeleted())->toBeTrue();

    // Challenge: should NOT be deleted (has acceptor)
    $acceptor = User::factory()->create();
    $activeChallenge = Challenge::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'acceptance_deadline' => now()->subDays(1),
        'acceptor_id' => $acceptor->id,
    ]);
    expect($activeChallenge->shouldBeDeleted())->toBeFalse();

    // Event: should be deleted
    $expiredEvent = GroupEvent::factory()->create([
        'group_id' => $group->id,
        'created_by_user_id' => $creator->id,
        'status' => 'upcoming',
        'event_date' => now()->subDays(1),
        'rsvp_deadline' => null,
    ]);
    expect($expiredEvent->shouldBeDeleted())->toBeTrue();

    // Event: should NOT be deleted (has RSVPs)
    $activeEvent = GroupEvent::factory()->create([
        'group_id' => $group->id,
        'created_by_user_id' => $creator->id,
        'status' => 'upcoming',
        'event_date' => now()->subDays(1),
        'rsvp_deadline' => null,
    ]);
    $rsvpUser = User::factory()->create();
    $activeEvent->rsvps()->create([
        'user_id' => $rsvpUser->id,
        'response' => 'going',
    ]);
    expect($activeEvent->shouldBeDeleted())->toBeFalse();
});
