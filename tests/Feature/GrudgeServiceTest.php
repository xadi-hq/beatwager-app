<?php

declare(strict_types=1);

use App\Models\AuditEvent;
use App\Models\Group;
use App\Models\User;
use App\Services\GrudgeService;

test('calculates head to head record correctly', function () {
    $user1 = User::factory()->create(['name' => 'Sarah']);
    $user2 = User::factory()->create(['name' => 'John']);
    $group = Group::factory()->create();

    // Create 3 wagers where Sarah wins
    for ($i = 0; $i < 3; $i++) {
        AuditEvent::create([
            'group_id' => $group->id,
            'event_type' => 'wager.won',
            'summary' => "Sarah won wager {$i}",
            'participants' => [
                ['user_id' => $user1->id, 'username' => 'Sarah', 'role' => 'winner'],
                ['user_id' => $user2->id, 'username' => 'John', 'role' => 'loser'],
            ],
            'impact' => ['points' => 50],
            'metadata' => ['wager_id' => fake()->uuid()],
            'created_at' => now()->subDays(3 - $i),
        ]);
    }

    $grudgeService = new GrudgeService();
    $history = $grudgeService->getRecentHistory($user1, $user2, $group);

    expect($history['wins'])->toBe(3);
    expect($history['losses'])->toBe(0);
    expect($history['narrative'])->toContain('3-wager winning streak');
    expect($history['narrative'])->toContain('Sarah');
    expect($history['recent_events'])->toHaveCount(3);
});

test('returns empty history when no wagers between users', function () {
    $user1 = User::factory()->create(['name' => 'Sarah']);
    $user2 = User::factory()->create(['name' => 'John']);
    $group = Group::factory()->create();

    $grudgeService = new GrudgeService();
    $history = $grudgeService->getRecentHistory($user1, $user2, $group);

    expect($history['wins'])->toBe(0);
    expect($history['losses'])->toBe(0);
    expect($history['narrative'])->toBeNull();
    expect($history['recent_events'])->toBeEmpty();
});

test('calculates mixed win loss record', function () {
    $user1 = User::factory()->create(['name' => 'Sarah']);
    $user2 = User::factory()->create(['name' => 'John']);
    $group = Group::factory()->create();

    // Sarah wins 2
    AuditEvent::create([
        'group_id' => $group->id,
        'event_type' => 'wager.won',
        'summary' => 'Sarah won wager 1',
        'participants' => [
            ['user_id' => $user1->id, 'username' => 'Sarah', 'role' => 'winner'],
            ['user_id' => $user2->id, 'username' => 'John', 'role' => 'loser'],
        ],
        'impact' => ['points' => 50],
        'created_at' => now()->subDays(3),
    ]);

    AuditEvent::create([
        'group_id' => $group->id,
        'event_type' => 'wager.won',
        'summary' => 'Sarah won wager 2',
        'participants' => [
            ['user_id' => $user1->id, 'username' => 'Sarah', 'role' => 'winner'],
            ['user_id' => $user2->id, 'username' => 'John', 'role' => 'loser'],
        ],
        'impact' => ['points' => 30],
        'created_at' => now()->subDays(2),
    ]);

    // John wins 1
    AuditEvent::create([
        'group_id' => $group->id,
        'event_type' => 'wager.won',
        'summary' => 'John won wager 3',
        'participants' => [
            ['user_id' => $user2->id, 'username' => 'John', 'role' => 'winner'],
            ['user_id' => $user1->id, 'username' => 'Sarah', 'role' => 'loser'],
        ],
        'impact' => ['points' => 20],
        'created_at' => now()->subDays(1),
    ]);

    $grudgeService = new GrudgeService();
    $history = $grudgeService->getRecentHistory($user1, $user2, $group);

    expect($history['wins'])->toBe(2);
    expect($history['losses'])->toBe(1);
    expect($history['narrative'])->toContain('dominating');
    expect($history['narrative'])->toContain('2 win(s) vs 1 loss(es)');
});

test('respects limit parameter', function () {
    $user1 = User::factory()->create(['name' => 'Sarah']);
    $user2 = User::factory()->create(['name' => 'John']);
    $group = Group::factory()->create();

    // Create 10 wagers
    for ($i = 0; $i < 10; $i++) {
        AuditEvent::create([
            'group_id' => $group->id,
            'event_type' => 'wager.won',
            'summary' => "Wager {$i}",
            'participants' => [
                ['user_id' => $user1->id, 'username' => 'Sarah', 'role' => 'winner'],
                ['user_id' => $user2->id, 'username' => 'John', 'role' => 'loser'],
            ],
            'impact' => ['points' => 50],
            'created_at' => now()->subDays(10 - $i),
        ]);
    }

    $grudgeService = new GrudgeService();
    $history = $grudgeService->getRecentHistory($user1, $user2, $group, limit: 3);

    expect($history['recent_events'])->toHaveCount(3);
});

test('generates even match narrative', function () {
    $user1 = User::factory()->create(['name' => 'Sarah']);
    $user2 = User::factory()->create(['name' => 'John']);
    $group = Group::factory()->create();

    // Each wins once
    AuditEvent::create([
        'group_id' => $group->id,
        'event_type' => 'wager.won',
        'summary' => 'Sarah won',
        'participants' => [
            ['user_id' => $user1->id, 'username' => 'Sarah', 'role' => 'winner'],
            ['user_id' => $user2->id, 'username' => 'John', 'role' => 'loser'],
        ],
        'impact' => ['points' => 50],
        'created_at' => now()->subDays(2),
    ]);

    AuditEvent::create([
        'group_id' => $group->id,
        'event_type' => 'wager.won',
        'summary' => 'John won',
        'participants' => [
            ['user_id' => $user2->id, 'username' => 'John', 'role' => 'winner'],
            ['user_id' => $user1->id, 'username' => 'Sarah', 'role' => 'loser'],
        ],
        'impact' => ['points' => 50],
        'created_at' => now()->subDays(1),
    ]);

    $grudgeService = new GrudgeService();
    $history = $grudgeService->getRecentHistory($user1, $user2, $group);

    expect($history['wins'])->toBe(1);
    expect($history['losses'])->toBe(1);
    expect($history['narrative'])->toContain('evenly matched');
    expect($history['narrative'])->toContain('1-1');
});
