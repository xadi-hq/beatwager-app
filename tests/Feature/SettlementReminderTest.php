<?php

use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Models\ShortUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

describe('Settlement Reminder Command', function () {
    it('sends reminders for unsettled wagers past deadline', function () {
        $creator = User::factory()->withTelegram()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        // Create wager past deadline
        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Past deadline wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'deadline' => now()->subDay(),
            'status' => 'open',
            'total_points_wagered' => 100,
            'participants_count' => 1,
        ]);

        // Run command
        Artisan::call('wagers:send-settlement-reminders');

        // Verify short URL was created
        expect(ShortUrl::count())->toBeGreaterThan(0);

        $shortUrl = ShortUrl::latest()->first();
        expect($shortUrl->target_url)->toContain('/wager/');
        expect($shortUrl->target_url)->toContain($wager->id);
    });

    it('ignores already settled wagers', function () {
        $creator = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        // Create settled wager past deadline
        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Settled wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'deadline' => now()->subDay(),
            'status' => 'settled',
            'outcome' => 'yes',
            'total_points_wagered' => 100,
            'participants_count' => 1,
        ]);

        $beforeCount = ShortUrl::count();

        Artisan::call('wagers:send-settlement-reminders');

        // No new short URLs should be created
        expect(ShortUrl::count())->toBe($beforeCount);
    });

    it('ignores wagers before deadline', function () {
        $creator = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000, 'role' => 'participant']);

        // Create wager with future deadline
        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Future wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'deadline' => now()->addDay(),
            'status' => 'open',
            'total_points_wagered' => 100,
            'participants_count' => 1,
        ]);

        $beforeCount = ShortUrl::count();

        Artisan::call('wagers:send-settlement-reminders');

        // No new short URLs should be created
        expect(ShortUrl::count())->toBe($beforeCount);
    });
});

describe('Short URL Generation', function () {
    it('generates unique short codes', function () {
        $code1 = ShortUrl::generateUniqueCode(6);
        $code2 = ShortUrl::generateUniqueCode(6);

        expect($code1)->not->toBe($code2);
        expect(strlen($code1))->toBe(6);
        expect(strlen($code2))->toBe(6);
    });

    it('creates short URL with expiration', function () {
        $shortUrl = ShortUrl::create([
            'code' => 'test123',
            'target_url' => 'https://example.com',
            'expires_at' => now()->addDays(30),
        ]);

        expect($shortUrl->code)->toBe('test123');
        expect($shortUrl->target_url)->toBe('https://example.com');
        expect($shortUrl->expires_at)->not->toBeNull();
    });
});
