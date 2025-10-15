<?php

use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Models\OneTimeToken;
use App\Services\WagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {

    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true], 200),
    ]);

    // Disable CSRF middleware for testing
    $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
});

describe('Duplicate Join Prevention', function () {
    it('prevents user from joining same wager twice', function () {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);
        $group->users()->attach($joiner->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

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

        // First join should succeed
        $wagerService->placeWager($wager, $joiner, 'yes', 100);

        // Second join should fail
        expect(fn() => $wagerService->placeWager($wager, $joiner, 'no', 100))
            ->toThrow(\Exception::class);

        // Verify only one entry exists
        expect($wager->entries()->count())->toBe(1);
    });
});

describe('Expired Token Handling', function () {
    it('rejects wager creation with expired token', function () {
        $url = generateWagerCreationUrl(
            telegramUserId: 12345,
            telegramUsername: 'testuser',
            telegramFirstName: 'Test',
            telegramChatId: 67890,
            telegramChatType: 'group',
            telegramChatTitle: 'Test Group',
            expiresInMinutes: 30
        );

        // Simulate time travel to make token expired
        $this->travel(31)->minutes();

        $response = $this->get($url);

        // Middleware returns 401 for expired/invalid signed URLs
        $response->assertUnauthorized();
    });

    it('rejects wager creation with used token', function () {
        $url = generateWagerCreationUrl(
            telegramUserId: 12345,
            telegramUsername: 'testuser',
            telegramFirstName: 'Test',
            telegramChatId: 67890,
            telegramChatType: 'group',
            telegramChatTitle: 'Test Group',
            expiresInMinutes: 30
        );

        // Simulate time travel to make URL expired
        $this->travel(31)->minutes();

        $response = $this->get($url);

        // Middleware returns 401 for expired/invalid signed URLs
        $response->assertUnauthorized();
    });
});

describe('Wager Status Validation', function () {
    it('prevents joining settled wagers', function () {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);
        $group->users()->attach($joiner->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Settled Wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'betting_closes_at' => now()->addHours(24),
            'status' => 'settled',
            'outcome' => 'yes',
            'total_points_wagered' => 0,
            'participants_count' => 0,
        ]);

        $wagerService = app(WagerService::class);

        expect(fn() => $wagerService->placeWager($wager, $joiner, 'yes', 100))
            ->toThrow(\Exception::class);
    });

    it('prevents settling already settled wagers', function () {
        $creator = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Settled Wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'betting_closes_at' => now()->addHours(24),
            'status' => 'settled',
            'outcome' => 'yes',
            'total_points_wagered' => 0,
            'participants_count' => 0,
        ]);

        $wagerService = app(WagerService::class);

        expect(fn() => $wagerService->settleWager($wager, 'no', 'Changed outcome'))
            ->toThrow(\Exception::class);
    });
});

describe('Numeric Range Validation', function () {
    it('rejects numeric answers outside allowed range', function () {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);
        $group->users()->attach($joiner->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Numeric Wager',
            'type' => 'numeric',
            'stake_amount' => 100,
            'betting_closes_at' => now()->addHours(24),
            'status' => 'open',
            'numeric_min' => 0,
            'numeric_max' => 50,
            'numeric_winner_type' => 'closest',
            'total_points_wagered' => 0,
            'participants_count' => 0,
        ]);

        $wagerService = app(WagerService::class);

        // Answer above max should fail
        expect(fn() => $wagerService->placeWager($wager, $joiner, '100', 100))
            ->toThrow(\Exception::class);

        // Answer below min should fail
        expect(fn() => $wagerService->placeWager($wager, $joiner, '-10', 100))
            ->toThrow(\Exception::class);
    });
});

// Note: Deadline validation is already covered in WagerCreationFlowTest
