<?php

declare(strict_types=1);

use App\Models\Group;
use App\Models\SentMessage;
use App\Services\MessageTrackingService;

test('allows message when no rules defined', function () {
    $group = Group::factory()->create();
    $service = new MessageTrackingService();

    $canSend = $service->canSendMessage($group, 'custom.message');

    expect($canSend)->toBeTrue();
});

test('prevents duplicate engagement prompts within 24 hours', function () {
    $group = Group::factory()->create();
    $contextId = fake()->uuid();
    $service = new MessageTrackingService();

    // First message should be allowed
    $canSend1 = $service->canSendMessage(
        $group,
        'engagement.prompt',
        'wager',
        $contextId
    );
    expect($canSend1)->toBeTrue();

    // Record it
    $service->recordMessage(
        $group,
        'engagement.prompt',
        'Test prompt',
        'wager',
        $contextId
    );

    // Second message within 24 hours should be blocked
    $canSend2 = $service->canSendMessage(
        $group,
        'engagement.prompt',
        'wager',
        $contextId
    );
    expect($canSend2)->toBeFalse();
});

test('allows engagement prompt after cooldown period', function () {
    $group = Group::factory()->create();
    $contextId = fake()->uuid();
    $service = new MessageTrackingService();

    // Record a message 25 hours ago
    SentMessage::create([
        'group_id' => $group->id,
        'message_type' => 'engagement.prompt',
        'context_type' => 'wager',
        'context_id' => $contextId,
        'sent_at' => now()->subHours(25),
    ]);

    // Should be allowed after cooldown
    $canSend = $service->canSendMessage(
        $group,
        'engagement.prompt',
        'wager',
        $contextId
    );
    expect($canSend)->toBeTrue();
});

test('enforces weekly recap limit', function () {
    $group = Group::factory()->create();
    $service = new MessageTrackingService();

    // Record a weekly recap 3 days ago
    SentMessage::create([
        'group_id' => $group->id,
        'message_type' => 'weekly.recap',
        'sent_at' => now()->subDays(3),
    ]);

    // Should not allow another one within the same week
    $canSend = $service->canSendMessage($group, 'weekly.recap');
    expect($canSend)->toBeFalse();
});

test('allows weekly recap after 7 days', function () {
    $group = Group::factory()->create();
    $service = new MessageTrackingService();

    // Record a weekly recap 8 days ago
    SentMessage::create([
        'group_id' => $group->id,
        'message_type' => 'weekly.recap',
        'sent_at' => now()->subDays(8),
    ]);

    // Should allow new recap
    $canSend = $service->canSendMessage($group, 'weekly.recap');
    expect($canSend)->toBeTrue();
});

test('records message with all metadata', function () {
    $group = Group::factory()->create();
    $service = new MessageTrackingService();

    $sentMessage = $service->recordMessage(
        $group,
        'engagement.prompt',
        'Low engagement on Marathon Bet',
        'wager',
        fake()->uuid(),
        ['participant_count' => 0, 'reason' => 'low_engagement']
    );

    expect($sentMessage)->toBeInstanceOf(SentMessage::class);
    expect($sentMessage->group_id)->toBe($group->id);
    expect($sentMessage->message_type)->toBe('engagement.prompt');
    expect($sentMessage->summary)->toBe('Low engagement on Marathon Bet');
    expect($sentMessage->metadata)->toHaveKey('participant_count');
    expect($sentMessage->metadata['reason'])->toBe('low_engagement');
});

test('retrieves recent message history', function () {
    $group = Group::factory()->create();
    $service = new MessageTrackingService();

    // Create several messages
    for ($i = 0; $i < 5; $i++) {
        SentMessage::create([
            'group_id' => $group->id,
            'message_type' => 'wager.announcement',
            'summary' => "Wager {$i} announced",
            'sent_at' => now()->subDays($i),
        ]);
    }

    $history = $service->getRecentHistory($group, 7);

    expect($history)->toHaveCount(5);
    expect($history[0]['type'])->toBe('wager.announcement');
    expect($history[0])->toHaveKeys(['type', 'summary', 'date']);
});

test('limits recent history to specified count', function () {
    $group = Group::factory()->create();
    $service = new MessageTrackingService();

    // Create 20 messages
    for ($i = 0; $i < 20; $i++) {
        SentMessage::create([
            'group_id' => $group->id,
            'message_type' => 'test.message',
            'summary' => "Message {$i}",
            'sent_at' => now()->subHours($i),
        ]);
    }

    $history = $service->getRecentHistory($group, 7);

    // Should only return 10 (hardcoded limit in service)
    expect($history)->toHaveCount(10);
});

test('isolates messages by group', function () {
    $group1 = Group::factory()->create();
    $group2 = Group::factory()->create();
    $service = new MessageTrackingService();

    // Record message for group1
    $service->recordMessage($group1, 'weekly.recap', 'Group 1 recap');

    // Should not affect group2
    $canSend = $service->canSendMessage($group2, 'weekly.recap');
    expect($canSend)->toBeTrue();
});

test('allows different context ids for same message type', function () {
    $group = Group::factory()->create();
    $service = new MessageTrackingService();

    $contextId1 = fake()->uuid();
    $contextId2 = fake()->uuid();

    // Send prompt for first wager
    $service->recordMessage(
        $group,
        'engagement.prompt',
        'Prompt 1',
        'wager',
        $contextId1
    );

    // Should allow prompt for different wager
    $canSend = $service->canSendMessage(
        $group,
        'engagement.prompt',
        'wager',
        $contextId2
    );
    expect($canSend)->toBeTrue();
});
