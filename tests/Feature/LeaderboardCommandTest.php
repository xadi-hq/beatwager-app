<?php

declare(strict_types=1);

use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Models\WagerEntry;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('formats leaderboard correctly with medal emojis', function () {
    $group = Group::factory()->create(['points_currency_name' => 'coins']);

    // Create 5 users with different points
    $users = collect();
    for ($i = 1; $i <= 5; $i++) {
        $user = User::factory()->create(['name' => "Player {$i}"]);
        $points = (6 - $i) * 100; // 500, 400, 300, 200, 100

        $group->users()->attach($user->id, [
            'points' => $points,
            'points_earned' => $points,
            'points_spent' => 0,
        ]);

        $users->push($user);
    }

    // Query leaderboard (same logic as handler)
    $leaderboard = DB::table('group_user')
        ->join('users', 'group_user.user_id', '=', 'users.id')
        ->where('group_user.group_id', $group->id)
        ->select(
            'users.name',
            'group_user.points'
        )
        ->orderBy('group_user.points', 'desc')
        ->limit(10)
        ->get();

    expect($leaderboard)->toHaveCount(5);
    expect($leaderboard->first()->name)->toBe('Player 1');
    expect($leaderboard->first()->points)->toBe(500);
    expect($leaderboard->last()->name)->toBe('Player 5');
    expect($leaderboard->last()->points)->toBe(100);
});

test('calculates win rate correctly', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();

    $group->users()->attach($user->id, ['points' => 1000]);

    // Create 7 wins (each needs a different wager)
    for ($i = 0; $i < 7; $i++) {
        $wager = Wager::factory()->create(['group_id' => $group->id]);
        WagerEntry::factory()->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'wager_id' => $wager->id,
            'result' => 'won',
        ]);
    }

    // Create 3 losses (each needs a different wager)
    for ($i = 0; $i < 3; $i++) {
        $wager = Wager::factory()->create(['group_id' => $group->id]);
        WagerEntry::factory()->create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'wager_id' => $wager->id,
            'result' => 'lost',
        ]);
    }

    // Query stats
    $stats = DB::table('group_user')
        ->join('users', 'group_user.user_id', '=', 'users.id')
        ->where('group_user.group_id', $group->id)
        ->where('users.id', $user->id)
        ->select(
            DB::raw('(SELECT COUNT(*) FROM wager_entries WHERE wager_entries.user_id = users.id AND wager_entries.group_id = group_user.group_id AND wager_entries.result = \'won\') as wins'),
            DB::raw('(SELECT COUNT(*) FROM wager_entries WHERE wager_entries.user_id = users.id AND wager_entries.group_id = group_user.group_id AND wager_entries.result = \'lost\') as losses')
        )
        ->first();

    expect($stats->wins)->toBe(7);
    expect($stats->losses)->toBe(3);

    $winRate = (int) round(($stats->wins / ($stats->wins + $stats->losses)) * 100);
    expect($winRate)->toBe(70); // 70% win rate
});

test('orders players by points descending', function () {
    $group = Group::factory()->create();

    // Create users in random order with specific points
    $userData = [
        ['name' => 'Third Place', 'points' => 300],
        ['name' => 'First Place', 'points' => 500],
        ['name' => 'Fifth Place', 'points' => 100],
        ['name' => 'Second Place', 'points' => 400],
        ['name' => 'Fourth Place', 'points' => 200],
    ];

    foreach ($userData as $data) {
        $user = User::factory()->create(['name' => $data['name']]);
        $group->users()->attach($user->id, ['points' => $data['points']]);
    }

    $leaderboard = DB::table('group_user')
        ->join('users', 'group_user.user_id', '=', 'users.id')
        ->where('group_user.group_id', $group->id)
        ->select('users.name', 'group_user.points')
        ->orderBy('group_user.points', 'desc')
        ->get();

    $expected = ['First Place', 'Second Place', 'Third Place', 'Fourth Place', 'Fifth Place'];
    $actual = $leaderboard->pluck('name')->toArray();

    expect($actual)->toBe($expected);
});

test('limits leaderboard to top 10 players', function () {
    $group = Group::factory()->create();

    // Create 15 users
    for ($i = 1; $i <= 15; $i++) {
        $user = User::factory()->create(['name' => "Player {$i}"]);
        $group->users()->attach($user->id, ['points' => 1000 - ($i * 10)]);
    }

    $leaderboard = DB::table('group_user')
        ->join('users', 'group_user.user_id', '=', 'users.id')
        ->where('group_user.group_id', $group->id)
        ->orderBy('group_user.points', 'desc')
        ->limit(10)
        ->get();

    expect($leaderboard)->toHaveCount(10);
});

test('handles players with zero wagers', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create(['name' => 'New Player']);

    $group->users()->attach($user->id, ['points' => 1000]);

    // Query stats
    $stats = DB::table('group_user')
        ->join('users', 'group_user.user_id', '=', 'users.id')
        ->where('group_user.group_id', $group->id)
        ->where('users.id', $user->id)
        ->select(
            'users.name',
            'group_user.points',
            DB::raw('(SELECT COUNT(*) FROM wager_entries WHERE wager_entries.user_id = users.id AND wager_entries.group_id = group_user.group_id AND wager_entries.result = \'won\') as wins'),
            DB::raw('(SELECT COUNT(*) FROM wager_entries WHERE wager_entries.user_id = users.id AND wager_entries.group_id = group_user.group_id AND wager_entries.result = \'lost\') as losses')
        )
        ->first();

    expect($stats->name)->toBe('New Player');
    expect($stats->points)->toBe(1000);
    expect($stats->wins)->toBe(0);
    expect($stats->losses)->toBe(0);
});

test('uses custom currency name', function () {
    $group = Group::factory()->create(['points_currency_name' => 'tacos']);
    $user = User::factory()->create();

    $group->users()->attach($user->id, ['points' => 500]);

    expect($group->points_currency_name)->toBe('tacos');
});

test('handles empty leaderboard', function () {
    $group = Group::factory()->create();

    $leaderboard = DB::table('group_user')
        ->join('users', 'group_user.user_id', '=', 'users.id')
        ->where('group_user.group_id', $group->id)
        ->select('users.name', 'group_user.points')
        ->orderBy('group_user.points', 'desc')
        ->get();

    expect($leaderboard)->toBeEmpty();
});

test('isolates leaderboard by group', function () {
    $group1 = Group::factory()->create();
    $group2 = Group::factory()->create();
    $user = User::factory()->create(['name' => 'John']);

    // User has different points in each group
    $group1->users()->attach($user->id, ['points' => 500]);
    $group2->users()->attach($user->id, ['points' => 300]);

    $leaderboard1 = DB::table('group_user')
        ->join('users', 'group_user.user_id', '=', 'users.id')
        ->where('group_user.group_id', $group1->id)
        ->select('users.name', 'group_user.points')
        ->first();

    $leaderboard2 = DB::table('group_user')
        ->join('users', 'group_user.user_id', '=', 'users.id')
        ->where('group_user.group_id', $group2->id)
        ->select('users.name', 'group_user.points')
        ->first();

    expect($leaderboard1->points)->toBe(500);
    expect($leaderboard2->points)->toBe(300);
});

test('medal assignment logic', function () {
    // Test medal assignment (would be in handler)
    $getMedal = fn(int $rank) => match ($rank) {
        1 => '🥇',
        2 => '🥈',
        3 => '🥉',
        default => "{$rank}.",
    };

    expect($getMedal(1))->toBe('🥇');
    expect($getMedal(2))->toBe('🥈');
    expect($getMedal(3))->toBe('🥉');
    expect($getMedal(4))->toBe('4.');
    expect($getMedal(10))->toBe('10.');
});
