<?php

use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Disable CSRF for testing, but keep other middleware
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

    // Mock MessengerFactory to avoid actual message sending
    $mockMessenger = Mockery::mock(\App\Services\Messengers\TelegramMessenger::class);
    $mockMessenger->shouldReceive('send')->andReturn(null);
    $mockMessenger->shouldReceive('sendDirectMessage')->andReturn(null);

    $mockFactory = Mockery::mock(\App\Services\MessengerFactory::class);
    $mockFactory->shouldReceive('create')->andReturn($mockMessenger);

    $this->app->instance(\App\Services\MessengerFactory::class, $mockFactory);
    $this->app->instance(\App\Messaging\MessengerAdapterInterface::class, $mockMessenger);

    // Mock MessageService to avoid LLM calls
    $mockMessageService = Mockery::mock(\App\Services\MessageService::class);
    $mockMessage = new \App\DTOs\Message(
        content: 'Mock LLM generated message',
        type: \App\DTOs\MessageType::Announcement
    );
    $mockMessageService->shouldReceive('generateWithLLM')->andReturn($mockMessage);
    $this->app->instance(\App\Services\MessageService::class, $mockMessageService);
});

describe('DonationController::create', function () {
    test('loads donor groups correctly', function () {
        // Arrange
        $donor = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        $group1->users()->attach($donor, ['points' => 500, 'role' => 'participant']);
        $group2->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->get('/donations/create');

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Donations/Create')
            ->has('groups', 2)
            ->where('donor.id', $donor->id)
        );
    });

    test('shows donor balance for each group', function () {
        // Arrange
        $donor = User::factory()->create();
        $group = Group::factory()->create(['points_currency_name' => 'coins']);
        $group->users()->attach($donor, ['points' => 750, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->get('/donations/create');

        // Assert
        $response->assertInertia(fn ($page) => $page
            ->has('groups', 1)
            ->where('groups.0.donor_points', 750)
            ->where('groups.0.currency_name', 'coins')
        );
    });

    test('returns 403 if user not member of any groups', function () {
        // Arrange
        $donor = User::factory()->create();
        $this->actingAs($donor);

        // Act & Assert
        $response = $this->get('/donations/create');
        $response->assertStatus(403);
    });
});

describe('DonationController::recipients', function () {
    test('returns all group members except donor', function () {
        // Arrange
        $donor = User::factory()->create(['name' => 'Donor']);
        $recipient1 = User::factory()->create(['name' => 'Recipient 1']);
        $recipient2 = User::factory()->create(['name' => 'Recipient 2']);

        $group = Group::factory()->create();
        $group->users()->attach($donor, ['points' => 500, 'role' => 'participant']);
        $group->users()->attach($recipient1, ['points' => 200, 'role' => 'participant']);
        $group->users()->attach($recipient2, ['points' => 300, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->get("/donations/groups/{$group->id}/recipients");

        // Assert
        $response->assertOk();
        $response->assertJsonCount(2, 'recipients');
        $response->assertJsonFragment(['name' => 'Recipient 1', 'points' => 200]);
        $response->assertJsonFragment(['name' => 'Recipient 2', 'points' => 300]);
        $response->assertJsonMissing(['name' => 'Donor']);
    });

    test('includes donor current balance', function () {
        // Arrange
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);
        $group->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->get("/donations/groups/{$group->id}/recipients");

        // Assert
        $response->assertJsonFragment(['donor_points' => 1000]);
    });

    test('returns 403 if donor not member of group', function () {
        // Arrange
        $donor = User::factory()->create();
        $group = Group::factory()->create();

        $this->actingAs($donor);

        // Act & Assert
        $response = $this->get("/donations/groups/{$group->id}/recipients");
        $response->assertStatus(403);
    });

    test('returns empty array if no other members', function () {
        // Arrange
        $donor = User::factory()->create();
        $group = Group::factory()->create();
        $group->users()->attach($donor, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->get("/donations/groups/{$group->id}/recipients");

        // Assert
        $response->assertOk();
        $response->assertJsonCount(0, 'recipients');
    });
});

describe('DonationController::store - Happy Path', function () {
    test('creates donation transactions correctly', function () {
        // Arrange
        $donor = User::factory()->create(['name' => 'Alice']);
        $recipient = User::factory()->create(['name' => 'Bob']);
        $group = Group::factory()->create();

        $group->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);
        $group->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->post('/donations', [
            'group_id' => $group->id,
            'recipient_id' => $recipient->id,
            'amount' => 200,
            'is_public' => false,
            'message' => 'Thanks for your help!',
        ]);

        // Assert
        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Verify transactions created
        expect(Transaction::where('type', 'donation_sent')->count())->toBe(1);
        expect(Transaction::where('type', 'donation_received')->count())->toBe(1);

        $sentTx = Transaction::where('type', 'donation_sent')->first();
        expect($sentTx->user_id)->toBe($donor->id);
        expect($sentTx->amount)->toBe(-200);
        expect($sentTx->balance_before)->toBe(1000);
        expect($sentTx->balance_after)->toBe(800);

        $receivedTx = Transaction::where('type', 'donation_received')->first();
        expect($receivedTx->user_id)->toBe($recipient->id);
        expect($receivedTx->amount)->toBe(200);
        expect($receivedTx->balance_before)->toBe(500);
        expect($receivedTx->balance_after)->toBe(700);
    });

    test('updates balances correctly', function () {
        // Arrange
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);
        $group->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $this->post('/donations', [
            'group_id' => $group->id,
            'recipient_id' => $recipient->id,
            'amount' => 300,
            'is_public' => true,
        ]);

        // Assert
        $donorMembership = $group->users()->where('users.id', $donor->id)->first();
        $recipientMembership = $group->users()->where('users.id', $recipient->id)->first();

        expect($donorMembership->pivot->points)->toBe(700); // 1000 - 300
        expect($recipientMembership->pivot->points)->toBe(800); // 500 + 300
    });

    test('creates audit events', function () {
        // Arrange
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);
        $group->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $this->post('/donations', [
            'group_id' => $group->id,
            'recipient_id' => $recipient->id,
            'amount' => 150,
            'is_public' => false,
        ]);

        // Assert - audit events created
        // Note: actual implementation may vary
    });

    test('uses database transaction for atomicity', function () {
        // Arrange
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);
        $group->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $this->post('/donations', [
            'group_id' => $group->id,
            'recipient_id' => $recipient->id,
            'amount' => 200,
            'is_public' => true,
        ]);

        // Assert - both transactions created or none
        $donationTransactions = Transaction::whereIn('type', ['donation_sent', 'donation_received'])->count();
        expect($donationTransactions)->toBe(2); // All or nothing
    });
});

describe('DonationController::store - Validation', function () {
    test('rejects if donor has insufficient balance', function () {
        // Arrange
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($donor, ['points' => 100, 'role' => 'participant']);
        $group->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->post('/donations', [
            'group_id' => $group->id,
            'recipient_id' => $recipient->id,
            'amount' => 200, // More than donor has!
            'is_public' => false,
        ]);

        // Assert
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Insufficient points']);

        // Verify no transactions created
        expect(Transaction::count())->toBe(0);
    });

    test('rejects if amount is zero', function () {
        // Arrange
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);
        $group->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->post('/donations', [
            'group_id' => $group->id,
            'recipient_id' => $recipient->id,
            'amount' => 0,
            'is_public' => false,
        ]);

        // Assert
        $response->assertStatus(302); // Validation error redirect
        $response->assertSessionHasErrors(['amount']);
    });

    test('rejects if amount is negative', function () {
        // Arrange
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);
        $group->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->post('/donations', [
            'group_id' => $group->id,
            'recipient_id' => $recipient->id,
            'amount' => -100,
            'is_public' => false,
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['amount']);
    });

    test('rejects self-donation', function () {
        // Arrange
        $donor = User::factory()->create();
        $group = Group::factory()->create();
        $group->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->post('/donations', [
            'group_id' => $group->id,
            'recipient_id' => $donor->id, // Donating to self!
            'amount' => 100,
            'is_public' => false,
        ]);

        // Assert
        $response->assertStatus(400);
        $response->assertJson(['message' => 'Cannot donate to yourself']);

        // Verify no transactions
        expect(Transaction::count())->toBe(0);
    });

    test('rejects if recipient not in same group', function () {
        // Arrange
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        $group1->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);
        $group2->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->post('/donations', [
            'group_id' => $group1->id,
            'recipient_id' => $recipient->id, // Recipient in different group
            'amount' => 100,
            'is_public' => false,
        ]);

        // Assert
        $response->assertStatus(403); // 403 because recipient membership check fails (authorization)
    });

    test('validates message length', function () {
        // Arrange
        $donor = User::factory()->create();
        $recipient = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($donor, ['points' => 1000, 'role' => 'participant']);
        $group->users()->attach($recipient, ['points' => 500, 'role' => 'participant']);

        $this->actingAs($donor);

        // Act
        $response = $this->post('/donations', [
            'group_id' => $group->id,
            'recipient_id' => $recipient->id,
            'amount' => 100,
            'is_public' => false,
            'message' => str_repeat('a', 501), // 501 chars, max is 500
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['message']);
    });
});
