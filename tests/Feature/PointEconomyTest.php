<?php

use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Services\WagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Point System', function () {
    it('initializes users with starting balance', function () {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $balance = $user->groups()->where('group_id', $group->id)->first()->pivot->points;
        expect($balance)->toBe(1000);
    });

    it('deducts points when joining wager', function () {
        $user = User::factory()->create();
        $creator = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($user->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);
        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Test Wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'betting_closes_at' => now()->addHours(24),
            'status' => 'open',
            'total_points_wagered' => 0,
            'participants_count' => 0,
        ]);

        $wagerService = app(WagerService::class);
        $wagerService->placeWager($wager, $user, 'yes', 100);

        $balance = $user->groups()->where('group_id', $group->id)->first()->pivot->points;
        expect($balance)->toBe(900); // 1000 - 100
    });

    it('prevents wagering with insufficient points', function () {
        $user = User::factory()->create();
        $creator = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($user->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 50, 'role' => 'participant']);
        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Test Wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'betting_closes_at' => now()->addHours(24),
            'status' => 'open',
            'total_points_wagered' => 0,
            'participants_count' => 0,
        ]);

        $wagerService = app(WagerService::class);

        expect(fn() => $wagerService->placeWager($wager, $user, 'yes', 100))
            ->toThrow(\Exception::class, 'Insufficient points');
    });

    it('distributes winnings correctly', function () {
        $creator = User::factory()->create();
        $winner = User::factory()->create();
        $loser = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);
        $group->users()->attach($winner->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);
        $group->users()->attach($loser->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Test Wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'betting_closes_at' => now()->addHours(24),
            'status' => 'open',
            'total_points_wagered' => 0,
            'participants_count' => 0,
        ]);

        $wagerService = app(WagerService::class);
        $wagerService->placeWager($wager, $winner, 'yes', 100);
        $wagerService->placeWager($wager, $loser, 'no', 100);

        $wagerService->settleWager($wager, 'yes', 'Winner takes all');

        $winnerBalance = $winner->groups()->where('group_id', $group->id)->first()->pivot->points;
        $loserBalance = $loser->groups()->where('group_id', $group->id)->first()->pivot->points;

        expect($winnerBalance)->toBe(1100); // 1000 - 100 + 200
        expect($loserBalance)->toBe(900);   // 1000 - 100
    });
});

describe('Transaction History', function () {
    it('creates transaction record when placing wager', function () {
        $user = User::factory()->create();
        $creator = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($user->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);
        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Test Wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'betting_closes_at' => now()->addHours(24),
            'status' => 'open',
            'total_points_wagered' => 0,
            'participants_count' => 0,
        ]);

        $wagerService = app(WagerService::class);
        $wagerService->placeWager($wager, $user, 'yes', 100);

        $transactions = \App\Models\Transaction::where('user_id', $user->id)->get();
        expect($transactions)->toHaveCount(1);

        $transaction = $transactions->first();
        expect($transaction->type)->toBe(\App\Enums\TransactionType::WagerPlaced);
        expect($transaction->amount)->toBe(-100);

        // Verify polymorphic relationship - transaction links to WagerEntry
        expect($transaction->transactionable_type)->toBe(\App\Models\WagerEntry::class);
        expect($transaction->transactionable)->not->toBeNull();
        expect($transaction->wagerEntry())->not->toBeNull();
        expect($transaction->getWager()->id)->toBe($wager->id);
    });

    it('creates transaction records when settling wager', function () {
        $creator = User::factory()->create();
        $winner = User::factory()->create();
        $loser = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);
        $group->users()->attach($winner->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);
        $group->users()->attach($loser->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Test Wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'betting_closes_at' => now()->addHours(24),
            'status' => 'open',
            'total_points_wagered' => 0,
            'participants_count' => 0,
        ]);

        $wagerService = app(WagerService::class);
        $wagerService->placeWager($wager, $winner, 'yes', 100);
        $wagerService->placeWager($wager, $loser, 'no', 100);

        $wagerService->settleWager($wager, 'yes', 'Settled');

        // Winner should have: wager_placed (-100) + wager_won (+200)
        $winnerTransactions = \App\Models\Transaction::where('user_id', $winner->id)->get();
        expect($winnerTransactions)->toHaveCount(2);
        expect($winnerTransactions->where('type', 'wager_won')->first()->amount)->toBe(200);

        // Loser should have: wager_placed (-100) + wager_lost (0 amount, just a record)
        $loserTransactions = \App\Models\Transaction::where('user_id', $loser->id)->get();
        expect($loserTransactions)->toHaveCount(2);
        expect($loserTransactions->where('type', 'wager_placed')->first())->not->toBeNull();
        expect($loserTransactions->where('type', 'wager_lost')->first())->not->toBeNull();
    });
});
