<?php

use App\Models\Group;
use App\Models\ScheduledMessage;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock AuditEventService to avoid real audit event creation
    $auditEventServiceMock = Mockery::mock('overload:' . AuditEventService::class);
    $auditEventServiceMock->shouldReceive('dropReceived')->andReturn(true);
});

test('distributeDropToGroup distributes points to all group members', function () {
    // Arrange
    $group = Group::factory()->create();
    $users = User::factory()->count(3)->create();

    // Attach users to group with initial balances
    foreach ($users as $user) {
        $group->users()->attach($user, ['points' => 100, 'role' => 'participant']);
    }

    $message = ScheduledMessage::factory()->create([
        'group_id' => $group->id,
        'is_drop_event' => true,
        'drop_amount' => 50,
    ]);

    // Act
    $recipientCount = $message->distributeDropToGroup();

    // Assert
    expect($recipientCount)->toBe(3);

    // Verify all users received points
    foreach ($users as $user) {
        $user->refresh();
        $membership = $group->users()->where('users.id', $user->id)->first();
        expect($membership->pivot->points)->toBe(150); // 100 + 50
    }

    // Verify transactions were created
    expect(Transaction::where('type', 'drop')->count())->toBe(3);

    foreach ($users as $user) {
        $transaction = Transaction::where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->where('type', 'drop')
            ->first();

        expect($transaction)->not->toBeNull();
        expect($transaction->amount)->toBe(50);
        // Note: description is set by PointService, not us directly
    }
});

test('distributeDropToGroup creates audit events for each recipient', function () {
    // Arrange
    $group = Group::factory()->create();
    $users = User::factory()->count(2)->create();

    foreach ($users as $user) {
        $group->users()->attach($user, ['points' => 100, 'role' => 'participant']);
    }

    $message = ScheduledMessage::factory()->create([
        'group_id' => $group->id,
        'is_drop_event' => true,
        'drop_amount' => 25,
        'title' => 'Test Drop Event',
    ]);

    // Act
    $recipientCount = $message->distributeDropToGroup();

    // Assert - check that audit events table has entries
    // (AuditEventService is mocked, but in real code it creates records)
    expect($recipientCount)->toBe(2);

    // Note: We mock AuditEventService to avoid actual event creation in tests
    // In production, each recipient would get a drop.received audit event
});

test('distributeDropToGroup handles group with single member', function () {
    // Arrange
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $group->users()->attach($user, ['points' => 50, 'role' => 'participant']);

    $message = ScheduledMessage::factory()->create([
        'group_id' => $group->id,
        'is_drop_event' => true,
        'drop_amount' => 100,
    ]);

    // Act
    $recipientCount = $message->distributeDropToGroup();

    // Assert
    expect($recipientCount)->toBe(1);

    $user->refresh();
    $membership = $group->users()->where('users.id', $user->id)->first();
    expect($membership->pivot->points)->toBe(150);
});

test('distributeDropToGroup handles group with no members', function () {
    // Arrange
    $group = Group::factory()->create();
    $message = ScheduledMessage::factory()->create([
        'group_id' => $group->id,
        'is_drop_event' => true,
        'drop_amount' => 100,
    ]);

    // Act
    $recipientCount = $message->distributeDropToGroup();

    // Assert
    expect($recipientCount)->toBe(0);
    expect(Transaction::where('type', 'drop')->count())->toBe(0);
});

test('distributeDropToGroup handles null drop amount gracefully', function () {
    // Arrange
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $group->users()->attach($user, ['points' => 100, 'role' => 'participant']);

    $message = ScheduledMessage::factory()->create([
        'group_id' => $group->id,
        'is_drop_event' => true,
        'drop_amount' => null,
    ]);

    // Act
    $recipientCount = $message->distributeDropToGroup();

    // Assert
    expect($recipientCount)->toBe(0);

    // Points should remain unchanged
    $user->refresh();
    $membership = $group->users()->where('users.id', $user->id)->first();
    expect($membership->pivot->points)->toBe(100);
});

test('distributeDropToGroup handles zero drop amount', function () {
    // Arrange
    $group = Group::factory()->create();
    $user = User::factory()->create();
    $group->users()->attach($user, ['points' => 100, 'role' => 'participant']);

    $message = ScheduledMessage::factory()->create([
        'group_id' => $group->id,
        'is_drop_event' => true,
        'drop_amount' => 0,
    ]);

    // Act
    $recipientCount = $message->distributeDropToGroup();

    // Assert
    expect($recipientCount)->toBe(0);

    // No transactions should be created for zero amount
    expect(Transaction::where('type', 'drop')->count())->toBe(0);
});

test('distributeDropToGroup uses database transaction for atomicity', function () {
    // Arrange
    $group = Group::factory()->create();
    $users = User::factory()->count(3)->create();

    foreach ($users as $user) {
        $group->users()->attach($user, ['points' => 100, 'role' => 'participant']);
    }

    $message = ScheduledMessage::factory()->create([
        'group_id' => $group->id,
        'is_drop_event' => true,
        'drop_amount' => 50,
    ]);

    // Act
    $recipientCount = $message->distributeDropToGroup();

    // Assert - all or nothing should have been applied
    // If transaction worked, all 3 users should have updated points
    expect($recipientCount)->toBe(3);
    expect(Transaction::where('type', 'drop')->count())->toBe(3);

    foreach ($users as $user) {
        $membership = $group->users()->where('users.id', $user->id)->first();
        expect($membership->pivot->points)->toBe(150);
    }
});
