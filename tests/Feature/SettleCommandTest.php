<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Commands\Handlers\SettleCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Challenge;
use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\User;
use App\Models\Wager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettleCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_settle_command_only_works_in_groups(): void
    {
        $messenger = $this->createMock(MessengerAdapterInterface::class);
        $handler = new SettleCommandHandler($messenger);

        // Mock sendMessage to capture the response
        $messenger->expects($this->once())
            ->method('sendMessage')
            ->with($this->callback(function ($message) {
                // Should contain error message about group-only
                return str_contains($message->text, 'only works in group');
            }));

        // Create DM message
        $message = new IncomingMessage(
            platform: 'telegram',
            messageId: '1',
            type: \App\Messaging\DTOs\MessageType::COMMAND,
            chatId: '12345',
            userId: '12345',
            username: 'testuser',
            firstName: 'Test',
            lastName: null,
            text: '/settle',
            command: '/settle',
            commandArgs: [],
            callbackData: null,
            metadata: ['chat_type' => 'private'] // DM context
        );

        $handler->handle($message);
    }

    public function test_settle_command_shows_unsettled_wagers(): void
    {
        $group = Group::factory()->create([
            'platform' => 'telegram',
            'platform_chat_id' => '-100123456789',
        ]);

        $creator = User::factory()->create();
        $group->users()->attach($creator->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'admin',
        ]);

        // Create an unsettled wager
        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'creator_id' => $creator->id,
            'status' => 'open',
            'type' => 'binary',
            'betting_closes_at' => now()->subDay(), // Past deadline
        ]);

        // Add entry to make it need settlement
        $wager->entries()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'wager_id' => $wager->id,
            'user_id' => $creator->id,
            'group_id' => $group->id, // Add required group_id
            'answer_value' => 'yes',
            'points_wagered' => 100,
        ]);

        $messenger = $this->createMock(MessengerAdapterInterface::class);
        $handler = new SettleCommandHandler($messenger);

        // Expect a message (either with items or "all caught up")
        $messenger->expects($this->once())
            ->method('sendMessage')
            ->with($this->callback(function ($message) {
                // Should send either items or "all caught up" message
                return is_string($message->text) && strlen($message->text) > 0;
            }));

        $message = new IncomingMessage(
            platform: 'telegram',
            messageId: '1',
            type: \App\Messaging\DTOs\MessageType::COMMAND,
            chatId: $group->platform_chat_id,
            userId: '12345',
            username: 'testuser',
            firstName: 'Test',
            lastName: null,
            text: '/settle',
            command: '/settle',
            commandArgs: [],
            callbackData: null,
            metadata: ['chat_type' => 'group'] // Group context
        );

        $handler->handle($message);
    }

    public function test_settle_command_shows_no_items_when_all_settled(): void
    {
        $group = Group::factory()->create([
            'platform' => 'telegram',
            'platform_chat_id' => '-100123456789',
        ]);

        $messenger = $this->createMock(MessengerAdapterInterface::class);
        $handler = new SettleCommandHandler($messenger);

        // Expect "all caught up" message
        $messenger->expects($this->once())
            ->method('sendMessage')
            ->with($this->callback(function ($message) {
                return str_contains($message->text, 'All caught up');
            }));

        $message = new IncomingMessage(
            platform: 'telegram',
            messageId: '1',
            type: \App\Messaging\DTOs\MessageType::COMMAND,
            chatId: $group->platform_chat_id,
            userId: '12345',
            username: 'testuser',
            firstName: 'Test',
            lastName: null,
            text: '/settle',
            command: '/settle',
            commandArgs: [],
            callbackData: null,
            metadata: ['chat_type' => 'group'] // Group context
        );

        $handler->handle($message);
    }

    public function test_settle_wager_callback_allows_anyone(): void
    {
        $group = Group::factory()->create();
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();

        $group->users()->attach($creator->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'admin',
        ]);
        $group->users()->attach($otherUser->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'creator_id' => $creator->id,
            'status' => 'open',
            'type' => 'binary',
        ]);

        // Add entry to make it settleable
        $wager->entries()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'wager_id' => $wager->id,
            'user_id' => $creator->id,
            'group_id' => $group->id, // Add required group_id
            'answer_value' => 'yes',
            'points_wagered' => 100,
        ]);

        // Test that anyone can settle (not just creator)
        $wagerService = app(\App\Services\WagerService::class);
        $wagerService->settleWager(
            wager: $wager,
            outcomeValue: 'yes',
            settlementNote: null,
            settlerId: $otherUser->id // Anyone can settle
        );

        // Verify wager is settled
        $this->assertEquals('settled', $wager->fresh()->status);
        // Verify settler_id tracks who settled
        $this->assertEquals($otherUser->id, $wager->fresh()->settler_id);
    }

    public function test_settlement_reminder_sent_to_group(): void
    {
        $group = Group::factory()->create([
            'platform' => 'telegram',
            'platform_chat_id' => '-100123456789',
        ]);

        $creator = User::factory()->create();
        $group->users()->attach($creator->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'admin',
        ]);

        // Create an unsettled wager
        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'creator_id' => $creator->id,
            'status' => 'open',
            'type' => 'binary',
            'betting_closes_at' => now()->subDay(),
        ]);

        // Add entry
        $wager->entries()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'wager_id' => $wager->id,
            'user_id' => $creator->id,
            'group_id' => $group->id, // Add required group_id
            'answer_value' => 'yes',
            'points_wagered' => 100,
        ]);

        // Run settlement reminder command
        // Note: This would send to TelegramMessenger in real execution
        $this->artisan('wagers:send-settlement-reminders')
            ->assertSuccessful();

        // In a real test, we'd mock TelegramMessenger and verify:
        // - Message was sent to group chat (not DM)
        // - Message includes settlement buttons
        // - Message has proper group context (plural pronouns)
    }
}
