<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

/**
 * Architecture Test: Database Schema Validation
 *
 * Ensures database schema matches what the code expects,
 * preventing column name mismatches and missing columns.
 */

test('group_user pivot table has correct points columns', function () {
    expect(Schema::hasColumn('group_user', 'points'))->toBeTrue();
    expect(Schema::hasColumn('group_user', 'points_earned'))->toBeTrue();
    expect(Schema::hasColumn('group_user', 'points_spent'))->toBeTrue();
});

test('users table does not have current_points column', function () {
    // This was a production bug - trying to sum('current_points') on users table
    expect(Schema::hasColumn('users', 'current_points'))->toBeFalse();
});

test('super_challenge_nudges has nudged_at not sent_at', function () {
    // This was a production bug - using wrong column name
    expect(Schema::hasColumn('super_challenge_nudges', 'nudged_at'))->toBeTrue();
    expect(Schema::hasColumn('super_challenge_nudges', 'sent_at'))->toBeFalse();
});

test('messenger_services table has required columns', function () {
    expect(Schema::hasColumn('messenger_services', 'platform'))->toBeTrue();
    expect(Schema::hasColumn('messenger_services', 'platform_user_id'))->toBeTrue();
    expect(Schema::hasColumn('messenger_services', 'user_id'))->toBeTrue();
});

test('challenges table has super challenge fields', function () {
    expect(Schema::hasColumn('challenges', 'type'))->toBeTrue();
    expect(Schema::hasColumn('challenges', 'prize_per_person'))->toBeTrue();
    expect(Schema::hasColumn('challenges', 'max_participants'))->toBeTrue();
    expect(Schema::hasColumn('challenges', 'evidence_guidance'))->toBeTrue();
});

test('groups table has super challenge settings', function () {
    expect(Schema::hasColumn('groups', 'superchallenge_frequency'))->toBeTrue();
    expect(Schema::hasColumn('groups', 'last_superchallenge_at'))->toBeTrue();
});
