<?php

use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Disable CSRF and signed auth middleware for business logic tests
uses()->beforeEach(function () {
    $this->withoutMiddleware([
        \App\Http\Middleware\VerifyCsrfToken::class,
        \App\Http\Middleware\AuthenticateFromSignedUrl::class,
    ]);
});

beforeEach(function () {
    // Mock TelegramMessenger to avoid actual Telegram API calls
    // The Telegram Bot SDK uses CURL, so Http::fake() doesn't work
    $mockMessenger = Mockery::mock(\App\Services\Messengers\TelegramMessenger::class);
    $mockMessenger->shouldReceive('send')->andReturn(null);

    $this->app->instance(\App\Services\Messengers\TelegramMessenger::class, $mockMessenger);
});

// Note: Laravel's signed URL functionality is tested by Laravel itself.
// We only test our business logic here.

describe('Wager Creation Form', function () {
    it('rejects expired token', function () {
        $url = generateWagerCreationUrl(
            telegramUserId: 12345,
            telegramUsername: 'testuser',
            telegramFirstName: 'Test',
            telegramChatId: 67890,
            telegramChatType: 'private',
            telegramChatTitle: null,
            expiresInMinutes: 30
        );

        $this->travel(31)->minutes();

        $response = $this->get("/wager/create?token={$url}");

        // Middleware returns 401 for expired/invalid tokens
        $response->assertUnauthorized();
    });

    it('rejects used token', function () {
        $url = generateWagerCreationUrl(
            telegramUserId: 12345,
            telegramUsername: 'testuser',
            telegramFirstName: 'Test',
            telegramChatId: 67890,
            telegramChatType: 'private',
            telegramChatTitle: null,
            expiresInMinutes: 30
        );

        $group = Group::factory()->create();
        $user = User::factory()->create();
        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'creator_id' => $user->id,
        ]);

        $token->markAsUsed();

        $response = $this->get("/wager/create?token={$url}");

        // Middleware returns 401 for expired/invalid tokens
        $response->assertUnauthorized();
    });
});

describe('Binary Wager Creation', function () {
    it('creates binary wager successfully', function () {
        $user = User::factory()->withTelegram()->create();
        $group = Group::factory()->create();
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $this->actingAs($user);

        $response = $this->post('/wager/store', [
            'title' => 'Will it rain tomorrow?',
            'description' => 'Weather bet',
            'type' => 'binary',
            'group_id' => $group->id,
            'stake_amount' => 100,
            'deadline' => now()->addDays(1)->toIso8601String(),
        ]);

        $response->assertRedirect();
        expect(Wager::count())->toBe(1);

        $wager = Wager::first();
        expect($wager->title)->toBe('Will it rain tomorrow?');
        expect($wager->type)->toBe('binary');
        expect($wager->stake_amount)->toBe(100);
        expect($wager->status)->toBe('open');
    });
});

describe('Multiple Choice Wager Creation', function () {
    it('creates multiple choice wager with options', function () {
        $user = User::factory()->withTelegram()->create();
        $group = Group::factory()->create();
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $this->actingAs($user);

        $response = $this->post('/wager/store', [
            'title' => 'Ajax vs PSV - Who wins?',
            'type' => 'multiple_choice',
            'group_id' => $group->id,
            'stake_amount' => 100,
            'deadline' => now()->addDays(1)->toIso8601String(),
            'options' => ['1', 'x', '2'],
        ]);

        $response->assertRedirect();
        $wager = Wager::first();

        expect($wager->type)->toBe('multiple_choice');
        expect($wager->options)->toBe(['1', 'x', '2']);
    });
});

describe('Numeric Wager Creation', function () {
    it('creates numeric wager with range', function () {
        $user = User::factory()->withTelegram()->create();
        $group = Group::factory()->create();
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $this->actingAs($user);

        $response = $this->post('/wager/store', [
            'title' => 'How many gold medals?',
            'type' => 'numeric',
            'group_id' => $group->id,
            'stake_amount' => 100,
            'deadline' => now()->addDays(1)->toIso8601String(),
            'numeric_min' => 0,
            'numeric_max' => 50,
            'numeric_winner_type' => 'closest',
        ]);

        $response->assertRedirect();
        $wager = Wager::first();

        expect($wager->type)->toBe('numeric');
        expect($wager->numeric_min)->toBe(0);
        expect($wager->numeric_max)->toBe(50);
        expect($wager->numeric_winner_type)->toBe('closest');
    });
});

describe('Date Wager Creation', function () {
    it('creates date wager with range', function () {
        $user = User::factory()->withTelegram()->create();
        $group = Group::factory()->create();
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $this->actingAs($user);

        $minDate = now()->addDays(1);
        $maxDate = now()->addDays(365);

        $response = $this->post('/wager/store', [
            'title' => 'When will X happen?',
            'type' => 'date',
            'group_id' => $group->id,
            'stake_amount' => 100,
            'deadline' => now()->addDays(1)->toIso8601String(),
            'date_min' => $minDate->toDateString(),
            'date_max' => $maxDate->toDateString(),
            'date_winner_type' => 'closest',
        ]);

        $response->assertRedirect();
        $wager = Wager::first();

        expect($wager->type)->toBe('date');
        expect($wager->date_min->toDateString())->toBe($minDate->toDateString());
        expect($wager->date_max->toDateString())->toBe($maxDate->toDateString());
        expect($wager->date_winner_type)->toBe('closest');
    });
});

describe('Validation', function () {
    it('requires title', function () {
        $user = User::factory()->withTelegram()->create();
        $group = Group::factory()->create();
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $this->actingAs($user);

        $response = $this->post('/wager/store', [
            'type' => 'binary',
            'group_id' => $group->id,
            'stake_amount' => 100,
            'deadline' => now()->addDays(1)->toIso8601String(),
        ]);

        $response->assertSessionHasErrors('title');
    });

    it('requires deadline in future', function () {
        $user = User::factory()->withTelegram()->create();
        $group = Group::factory()->create();
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $this->actingAs($user);

        $response = $this->post('/wager/store', [
            'title' => 'Test wager',
            'type' => 'binary',
            'group_id' => $group->id,
            'stake_amount' => 100,
            'deadline' => now()->subDay()->toIso8601String(),
        ]);

        $response->assertSessionHasErrors('deadline');
    });

    it('requires positive stake amount', function () {
        $user = User::factory()->withTelegram()->create();
        $group = Group::factory()->create();
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $this->actingAs($user);

        $response = $this->post('/wager/store', [
            'title' => 'Test wager',
            'type' => 'binary',
            'group_id' => $group->id,
            'stake_amount' => 0,
            'deadline' => now()->addDays(1)->toIso8601String(),
        ]);

        $response->assertSessionHasErrors('stake_amount');
    });
});
