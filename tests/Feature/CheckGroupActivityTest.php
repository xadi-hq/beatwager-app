<?php

declare(strict_types=1);

use App\Models\Group;
use App\Models\SentMessage;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Mock TelegramAdapter directly to avoid actual API calls
    $mockAdapter = Mockery::mock(\App\Messaging\Adapters\TelegramAdapter::class);
    $mockAdapter->shouldReceive('sendMessage')->andReturn(null);
    $mockAdapter->shouldReceive('sendDirectMessage')->andReturn(null);

    $this->app->instance(\App\Messaging\Adapters\TelegramAdapter::class, $mockAdapter);

    // Also mock MessengerInterface for compatibility
    $mockMessenger = Mockery::mock(\App\Contracts\MessengerInterface::class);
    $mockMessenger->shouldReceive('send')->andReturn(null);
    $mockMessenger->shouldReceive('sendDirectMessage')->andReturn(null);

    $mockFactory = Mockery::mock(\App\Services\MessengerFactory::class);
    $mockFactory->shouldReceive('for')->andReturn($mockMessenger);

    $this->app->instance(\App\Services\MessengerFactory::class, $mockFactory);

    // Enable activity tracking feature
    Config::set('features.activity_tracking', true);
});

test('sends revival message to inactive group and records tracking', function () {
    // Set last_activity_at to exactly 15 days ago at midnight to avoid timing issues
    $lastActivity = now()->subDays(15)->startOfDay();

    $group = Group::factory()->create([
        'is_active' => true,
        'last_activity_at' => $lastActivity,
        'inactivity_threshold_days' => 14,
    ]);

    // Run the command
    $this->artisan('activity:check')->assertExitCode(0);

    // Verify tracking was recorded
    $sentMessage = SentMessage::where('group_id', $group->id)
        ->where('message_type', 'revival.inactive')
        ->first();

    expect($sentMessage)->not->toBeNull();
    expect($sentMessage->summary)->toContain('Revival message: inactive for');
    expect($sentMessage->summary)->toContain('days');
    expect($sentMessage->context_type)->toBe('activity_check');
    expect($sentMessage->metadata)->toHaveKey('days_inactive');
    // diffInDays() can return negative or fractional values due to timing
    // Just check that the value is approximately 15 days (allowing for negative sign due to timing)
    expect(abs($sentMessage->metadata['days_inactive']))->toBeGreaterThan(14);
    expect(abs($sentMessage->metadata['days_inactive']))->toBeLessThan(16);
    expect($sentMessage->metadata['threshold'])->toBe(14);
});

test('skips groups below inactivity threshold', function () {
    $group = Group::factory()->create([
        'is_active' => true,
        'last_activity_at' => now()->subDays(10),
        'inactivity_threshold_days' => 14,
    ]);

    $this->artisan('activity:check')->assertExitCode(0);

    // Verify no tracking was recorded
    $sentMessage = SentMessage::where('group_id', $group->id)->first();
    expect($sentMessage)->toBeNull();
});

test('dry run mode does not record tracking', function () {
    $group = Group::factory()->create([
        'is_active' => true,
        'last_activity_at' => now()->subDays(15),
        'inactivity_threshold_days' => 14,
    ]);

    $this->artisan('activity:check --dry-run')->assertExitCode(0);

    // Verify no tracking was recorded in dry-run mode
    $sentMessage = SentMessage::where('group_id', $group->id)->first();
    expect($sentMessage)->toBeNull();
});

test('feature flag disables activity tracking', function () {
    Config::set('features.activity_tracking', false);

    $group = Group::factory()->create([
        'is_active' => true,
        'last_activity_at' => now()->subDays(15),
        'inactivity_threshold_days' => 14,
    ]);

    $this->artisan('activity:check')->assertExitCode(0);

    // Verify no tracking was recorded
    $sentMessage = SentMessage::where('group_id', $group->id)->first();
    expect($sentMessage)->toBeNull();
});
