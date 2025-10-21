<?php

use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Services\MessageService;
use App\Services\MessengerFactory;
use App\Services\Messengers\TelegramMessenger;
use App\DTOs\Message;
use App\DTOs\MessageType;
use App\DTOs\Button;
use App\DTOs\ButtonAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock Telegram API calls
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true], 200),
    ]);
});

describe('MessageService', function () {
    it('generates wager announcement message', function () {
        $creator = User::factory()->create();
        $group = Group::factory()->create();

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Will it rain?',
            'description' => 'Weather bet',
            'type' => 'binary',
            'stake_amount' => 100,
            'betting_closes_at' => now()->addDay(),
            'status' => 'open',
            'total_points_wagered' => 0,
            'participants_count' => 0,
        ]);

        $messageService = app(MessageService::class);
        $message = $messageService->wagerAnnouncement($wager);

        expect($message)->toBeInstanceOf(Message::class);
        expect($message->type)->toBe(MessageType::Announcement);
        expect($message->content)->toBeString();
        expect($message->content)->not->toBeEmpty();
        expect($message->buttons)->toHaveCount(4); // yes, no, track progress, view details
    });

    it('generates settlement reminder message', function () {
        $creator = User::factory()->create();
        $group = Group::factory()->create();

        $wager = Wager::create([
            'creator_id' => $creator->id,
            'group_id' => $group->id,
            'title' => 'Past deadline wager',
            'type' => 'binary',
            'stake_amount' => 100,
            'betting_closes_at' => now()->subDay(),
            'status' => 'open',
            'total_points_wagered' => 200,
            'participants_count' => 2,
        ]);

        $messageService = app(MessageService::class);
        $message = $messageService->settlementReminder($wager, 'https://example.com/short');

        expect($message)->toBeInstanceOf(Message::class);
        expect($message->type)->toBe(MessageType::Reminder);
        expect($message->content)->toBeString();
        expect($message->content)->not->toBeEmpty();
        expect($message->buttons)->toHaveCount(1); // settle button
    });

    it('generates join confirmation message', function () {
        $messageService = app(MessageService::class);
        $message = $messageService->joinConfirmation();

        expect($message)->toBeInstanceOf(Message::class);
        expect($message->type)->toBe(MessageType::Confirmation);
    });
});

describe('MessengerFactory', function () {
    it('resolves telegram messenger for telegram groups', function () {
        $group = Group::factory()->create(['platform' => 'telegram']);

        $messenger = MessengerFactory::for($group);

        expect($messenger)->toBeInstanceOf(TelegramMessenger::class);
    });

    it('throws exception for unsupported platforms', function () {
        $group = Group::factory()->create(['platform' => 'unsupported']);

        expect(fn() => MessengerFactory::for($group))
            ->toThrow(\Exception::class, 'Unsupported messenger platform');
    });
});

describe('Group Messaging', function () {
    it('sends message through correct platform', function () {
        $group = Group::factory()->create([
            'platform' => 'telegram',
            'platform_chat_id' => '12345',
        ]);

        $messageService = app(MessageService::class);
        $message = $messageService->joinConfirmation();

        // Verify MessengerFactory resolves correctly
        $messenger = \App\Services\MessengerFactory::for($group);
        expect($messenger)->toBeInstanceOf(\App\Services\Messengers\TelegramMessenger::class);

        // Note: Actual sending is mocked via HTTP::fake in beforeEach
        // This test verifies the factory pattern works correctly
    });

    it('retrieves correct chat ID based on platform', function () {
        $group = Group::factory()->create([
            'platform' => 'telegram',
            'platform_chat_id' => '98765',
        ]);

        $chatId = $group->getChatId();

        expect($chatId)->toBe('98765');
    });
});

describe('Message Formatting', function () {
    it('formats message content with variables', function () {
        $message = new Message(
            content: 'Hello {name}, you have {points} points',
            type: MessageType::Info,
            variables: ['name' => 'Alice', 'points' => '1000']
        );

        $formatted = $message->getFormattedContent();

        expect($formatted)->toBe('Hello Alice, you have 1000 points');
    });

    it('handles messages without variables', function () {
        $message = new Message(
            content: 'Static message',
            type: MessageType::Info
        );

        $formatted = $message->getFormattedContent();

        expect($formatted)->toBe('Static message');
    });
});
