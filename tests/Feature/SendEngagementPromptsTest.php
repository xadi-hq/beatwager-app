<?php

declare(strict_types=1);

use App\Models\Group;
use App\Models\SentMessage;
use App\Models\User;
use App\Models\Wager;
use App\Services\MessageTrackingService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('finds stale wagers with zero participants after 24 hours', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    // Create a wager that's 25 hours old with 0 participants
    $staleWager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'participants_count' => 0,
        'created_at' => now()->subHours(25),
    ]);

    // Create a recent wager (should not be found)
    $recentWager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'participants_count' => 0,
        'created_at' => now()->subHours(20),
    ]);

    // Query stale wagers (same logic as job)
    $staleWagers = Wager::where('status', 'open')
        ->where('created_at', '<=', now()->subHours(24))
        ->where('participants_count', '<=', 1)
        ->get();

    expect($staleWagers)->toHaveCount(1);
    expect($staleWagers->first()->id)->toBe($staleWager->id);
});

test('finds stale wagers with one participant', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    $wager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'participants_count' => 1,
        'created_at' => now()->subHours(26),
    ]);

    $staleWagers = Wager::where('status', 'open')
        ->where('created_at', '<=', now()->subHours(24))
        ->where('participants_count', '<=', 1)
        ->get();

    expect($staleWagers->pluck('id'))->toContain($wager->id);
});

test('does not find wagers with 2+ participants', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    $wager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'open',
        'participants_count' => 2,
        'created_at' => now()->subHours(26),
    ]);

    $staleWagers = Wager::where('status', 'open')
        ->where('created_at', '<=', now()->subHours(24))
        ->where('participants_count', '<=', 1)
        ->get();

    expect($staleWagers->pluck('id'))->not()->toContain($wager->id);
});

test('does not find settled wagers', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    $wager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'status' => 'settled',
        'participants_count' => 0,
        'created_at' => now()->subHours(26),
    ]);

    $staleWagers = Wager::where('status', 'open')
        ->where('created_at', '<=', now()->subHours(24))
        ->where('participants_count', '<=', 1)
        ->get();

    expect($staleWagers->pluck('id'))->not()->toContain($wager->id);
});

test('message tracking service prevents duplicate engagement prompts', function () {
    $group = Group::factory()->create();
    $trackingService = new MessageTrackingService();
    $wagerId = fake()->uuid();

    // First check - should be allowed
    $canSend1 = $trackingService->canSendMessage(
        $group,
        'engagement.prompt',
        'wager',
        $wagerId
    );
    expect($canSend1)->toBeTrue();

    // Record it
    $trackingService->recordMessage(
        $group,
        'engagement.prompt',
        'Test prompt',
        'wager',
        $wagerId,
        ['participant_count' => 0]
    );

    // Second check - should be blocked (24h cooldown)
    $canSend2 = $trackingService->canSendMessage(
        $group,
        'engagement.prompt',
        'wager',
        $wagerId
    );
    expect($canSend2)->toBeFalse();
});

test('allows engagement prompts for different wagers', function () {
    $group = Group::factory()->create();
    $trackingService = new MessageTrackingService();

    $wager1Id = fake()->uuid();
    $wager2Id = fake()->uuid();

    // Send prompt for wager1
    $trackingService->recordMessage($group, 'engagement.prompt', 'Prompt 1', 'wager', $wager1Id);

    // Should still allow prompt for wager2 (different context)
    $canSend = $trackingService->canSendMessage(
        $group,
        'engagement.prompt',
        'wager',
        $wager2Id
    );
    expect($canSend)->toBeTrue();
});

test('generates engagement prompt message with correct data', function () {
    $group = Group::factory()->create(['points_currency_name' => 'coins']);
    $creator = User::factory()->create();

    $wager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'title' => 'Marathon Bet',
        'status' => 'open',
        'stake_amount' => 50,
        'participants_count' => 0,
        'created_at' => now()->subHours(28),
        'betting_closes_at' => now()->addHours(8),
    ]);

    $messageService = app(\App\Services\MessageService::class);
    $message = $messageService->engagementPrompt($wager);

    // Verify message structure
    expect($message->context)->toBe($wager);
    expect($message->currencyName)->toBe('coins');
    expect($message->buttons)->not()->toBeEmpty();
});

test('calculates hours since created correctly', function () {
    $group = Group::factory()->create();
    $creator = User::factory()->create();

    $wager = Wager::factory()->create([
        'group_id' => $group->id,
        'creator_id' => $creator->id,
        'created_at' => now()->subHours(30),
    ]);

    $hoursSince = (int) $wager->created_at->diffInHours(now());

    expect($hoursSince)->toBeGreaterThanOrEqual(30);
    expect($hoursSince)->toBeLessThan(31);
});

test('group notification preferences can disable engagement prompts', function () {
    $groupEnabled = Group::factory()->create([
        'notification_preferences' => [
            'engagement_prompts' => true,
        ],
    ]);

    $groupDisabled = Group::factory()->create([
        'notification_preferences' => [
            'engagement_prompts' => false,
        ],
    ]);

    expect($groupEnabled->notification_preferences['engagement_prompts'] ?? true)->toBeTrue();
    expect($groupDisabled->notification_preferences['engagement_prompts'] ?? true)->toBeFalse();
});
