<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\MessengerService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Chat;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\User as TelegramUser;

class TelegramUserAutoRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Group $group;
    private string $chatId;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable activity tracking feature
        Config::set('features.activity_tracking', true);

        // Disable webhook secret validation for testing
        Config::set('telegram.webhook.secret', null);

        // Create a test group
        $this->chatId = '-1001234567890';
        $this->group = Group::create([
            'name' => 'Test Group',
            'platform' => 'telegram',
            'platform_chat_id' => $this->chatId,
            'platform_chat_title' => 'Test Group',
            'platform_chat_type' => 'supergroup',
            'starting_balance' => 1000,
        ]);

        // Mock BotApi to prevent actual API calls
        $this->mock(BotApi::class);
    }

    /** @test */
    public function it_adds_new_member_when_user_joins_group(): void
    {
        // Arrange: Create Telegram update for new member
        $telegramUser = $this->createTelegramUser(123456789, 'testuser', 'Test', 'User');
        $update = $this->createMemberJoinUpdate($telegramUser);

        // Act: Send webhook
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));

        // Assert: User was created and added to group
        $response->assertOk();

        $messengerService = MessengerService::findByPlatform('telegram', '123456789');
        $this->assertNotNull($messengerService);
        $user = $messengerService->user;
        $this->assertEquals('Test User', $user->name);

        $this->assertTrue($this->group->users()->where('user_id', $user->id)->exists());

        $pivot = $this->group->users()->where('user_id', $user->id)->first()->pivot;
        $this->assertEquals(1000, $pivot->points);
        $this->assertEquals('participant', $pivot->role);
    }

    /** @test */
    public function it_removes_member_when_user_leaves_group(): void
    {
        // Arrange: Create user and add to group
        $user = User::create([
            'name' => 'Test User',
        ]);

        // Create messenger service for user
        MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'telegram',
            'platform_user_id' => '123456789',
            'username' => 'testuser',
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $this->group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 500,
            'role' => 'participant',
        ]);

        // Create Telegram update for member leaving
        $telegramUser = $this->createTelegramUser(123456789, 'testuser', 'Test', 'User');
        $update = $this->createMemberLeftUpdate($telegramUser);

        // Act: Send webhook
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));

        // Assert: User was removed from group
        $response->assertOk();
        $this->assertFalse($this->group->fresh()->users()->where('user_id', $user->id)->exists());
    }

    /** @test */
    public function it_caches_user_points_when_leaving_for_rejoin_protection(): void
    {
        // Arrange: Create user with specific points
        $user = User::create([
            'name' => 'Test User',
        ]);

        // Create messenger service for user
        MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'telegram',
            'platform_user_id' => '123456789',
            'username' => 'testuser',
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $this->group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 750,
            'role' => 'participant',
        ]);

        // Create Telegram update for member leaving
        $telegramUser = $this->createTelegramUser(123456789, 'testuser', 'Test', 'User');
        $update = $this->createMemberLeftUpdate($telegramUser);

        // Act: User leaves
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));
        $response->assertOk();

        // Assert: Points are cached
        $rejoinKey = "group_rejoin:{$this->group->id}:{$user->id}";
        $cachedPoints = Cache::get($rejoinKey);

        $this->assertEquals(750, $cachedPoints);
    }

    /** @test */
    public function it_restores_previous_points_when_user_rejoins_within_72_hours(): void
    {
        // Arrange: User previously had 750 points
        $user = User::create([
            'name' => 'Test User',
        ]);

        MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'telegram',
            'platform_user_id' => '123456789',
            'username' => 'testuser',
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $rejoinKey = "group_rejoin:{$this->group->id}:{$user->id}";
        Cache::put($rejoinKey, 750, now()->addHours(72));

        // Create Telegram update for user rejoining
        $telegramUser = $this->createTelegramUser(123456789, 'testuser', 'Test', 'User');
        $update = $this->createMemberJoinUpdate($telegramUser);

        // Act: User rejoins
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));

        // Assert: Previous points restored
        $response->assertOk();

        $pivot = $this->group->fresh()->users()->where('user_id', $user->id)->first()->pivot;
        $this->assertEquals(750, $pivot->points, 'Should restore previous points, not starting balance');
    }

    /** @test */
    public function it_assigns_starting_balance_when_rejoin_cache_expired(): void
    {
        // Arrange: Cache expired (no cached points)
        $telegramUser = $this->createTelegramUser(123456789, 'testuser', 'Test', 'User');
        $update = $this->createMemberJoinUpdate($telegramUser);

        // Act: User joins
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));

        // Assert: Starting balance assigned
        $response->assertOk();

        $messengerService = MessengerService::findByPlatform('telegram', '123456789');
        $user = $messengerService->user;
        $pivot = $this->group->fresh()->users()->where('user_id', $user->id)->first()->pivot;

        $this->assertEquals(1000, $pivot->points, 'Should assign starting balance when no cache');
    }

    /** @test */
    public function it_auto_registers_user_from_group_message(): void
    {
        // Arrange: User sends message in group (not a member yet)
        $telegramUser = $this->createTelegramUser(987654321, 'newuser', 'New', 'User');
        $update = $this->createMessageUpdate($telegramUser, 'Hello everyone!');

        // Act: Send webhook
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));

        // Assert: User auto-registered
        $response->assertOk();

        $messengerService = MessengerService::findByPlatform('telegram', '987654321');
        $this->assertNotNull($messengerService);
        $user = $messengerService->user;

        $this->assertTrue($this->group->fresh()->users()->where('user_id', $user->id)->exists());

        $pivot = $this->group->fresh()->users()->where('user_id', $user->id)->first()->pivot;
        $this->assertEquals(1000, $pivot->points);
    }

    /** @test */
    public function it_updates_last_activity_for_existing_member_on_message(): void
    {
        // Arrange: Existing member (create via UserMessengerService to ensure proper setup)
        $user = User::create([
            'name' => 'Existing User',
        ]);

        // Create messenger service for user
        MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'telegram',
            'platform_user_id' => '555555555',
            'username' => 'existinguser',
            'first_name' => 'Existing',
            'last_name' => 'User',
        ]);

        $oldTimestamp = now()->subHours(5);
        $this->group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 800,
            'role' => 'participant',
            'last_activity_at' => $oldTimestamp,
        ]);

        // Add small delay to ensure time difference
        sleep(1);

        // User sends message
        $telegramUser = $this->createTelegramUser(555555555, 'existinguser', 'Existing', 'User');
        $update = $this->createMessageUpdate($telegramUser, 'Still here!');

        // Act: Send webhook
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));
        $response->assertOk();

        // Assert: last_activity_at updated
        $pivot = $this->group->fresh()->users()->where('user_id', $user->id)->first()->pivot;
        $this->assertTrue($pivot->last_activity_at->isAfter($oldTimestamp));
    }

    /** @test */
    public function it_skips_bot_users_when_processing_member_updates(): void
    {
        // Arrange: Bot user joins
        $botUser = $this->createTelegramUser(999999999, 'testbot', 'Test', 'Bot', isBot: true);
        $update = $this->createMemberJoinUpdate($botUser);

        // Act: Send webhook
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));

        // Assert: Bot not added to group
        $response->assertOk();

        $messengerService = MessengerService::findByPlatform('telegram', '999999999');
        $this->assertNull($messengerService, 'Bot should not be created in users table');
    }

    /** @test */
    public function it_handles_multiple_users_joining_at_once(): void
    {
        // Arrange: Multiple users join (e.g., via invite link)
        $user1 = $this->createTelegramUser(111111111, 'user1', 'User', 'One');
        $user2 = $this->createTelegramUser(222222222, 'user2', 'User', 'Two');

        $update = $this->createMultipleMembersJoinUpdate([$user1, $user2]);

        // Act: Send webhook
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));

        // Assert: Both users added
        $response->assertOk();

        $this->assertNotNull(MessengerService::findByPlatform('telegram', '111111111'));
        $this->assertNotNull(MessengerService::findByPlatform('telegram', '222222222'));

        $this->assertEquals(2, $this->group->fresh()->users()->count());
    }

    /** @test */
    public function it_does_not_process_private_chat_messages(): void
    {
        // Arrange: Message in private chat (not group)
        $telegramUser = $this->createTelegramUser(123456789, 'testuser', 'Test', 'User');
        $update = $this->createPrivateMessageUpdate($telegramUser, 'Hello bot!');

        // Act: Send webhook
        $response = $this->postJson('/api/telegram/webhook', $update->toJson(true));

        // Assert: User not auto-registered (no group context)
        $response->assertOk();

        $messengerService = MessengerService::findByPlatform('telegram', '123456789');
        // User might be created by command handler, but shouldn't be added to any group
        if ($messengerService) {
            $user = $messengerService->user;
            $this->assertFalse($this->group->fresh()->users()->where('user_id', $user->id)->exists());
        }
    }

    // Helper Methods

    private function createTelegramUser(
        int $id,
        string $username,
        string $firstName,
        string $lastName,
        bool $isBot = false
    ): TelegramUser {
        return TelegramUser::fromResponse([
            'id' => $id,
            'username' => $username,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'is_bot' => $isBot,
        ]);
    }

    private function createChat(string $type = 'supergroup'): Chat
    {
        return Chat::fromResponse([
            'id' => (int)$this->chatId,
            'type' => $type,
            'title' => 'Test Group',
        ]);
    }

    private function createMemberJoinUpdate(TelegramUser $user): Update
    {
        $message = Message::fromResponse([
            'message_id' => random_int(1000, 9999),
            'date' => time(),
            'chat' => $this->createChat()->toJson(true),
            'from' => $user->toJson(true),
            'new_chat_members' => [$user->toJson(true)],
        ]);

        return Update::fromResponse([
            'update_id' => random_int(10000, 99999),
            'message' => $message->toJson(true),
        ]);
    }

    private function createMultipleMembersJoinUpdate(array $users): Update
    {
        $usersData = array_map(fn($u) => $u->toJson(true), $users);

        $message = Message::fromResponse([
            'message_id' => random_int(1000, 9999),
            'date' => time(),
            'chat' => $this->createChat()->toJson(true),
            'from' => $users[0]->toJson(true),
            'new_chat_members' => $usersData,
        ]);

        return Update::fromResponse([
            'update_id' => random_int(10000, 99999),
            'message' => $message->toJson(true),
        ]);
    }

    private function createMemberLeftUpdate(TelegramUser $user): Update
    {
        $message = Message::fromResponse([
            'message_id' => random_int(1000, 9999),
            'date' => time(),
            'chat' => $this->createChat()->toJson(true),
            'from' => $user->toJson(true),
            'left_chat_member' => $user->toJson(true),
        ]);

        return Update::fromResponse([
            'update_id' => random_int(10000, 99999),
            'message' => $message->toJson(true),
        ]);
    }

    private function createMessageUpdate(TelegramUser $user, string $text): Update
    {
        $message = Message::fromResponse([
            'message_id' => random_int(1000, 9999),
            'date' => time(),
            'chat' => $this->createChat()->toJson(true),
            'from' => $user->toJson(true),
            'text' => $text,
        ]);

        return Update::fromResponse([
            'update_id' => random_int(10000, 99999),
            'message' => $message->toJson(true),
        ]);
    }

    private function createPrivateMessageUpdate(TelegramUser $user, string $text): Update
    {
        $privateChat = Chat::fromResponse([
            'id' => $user->getId(),
            'type' => 'private',
            'first_name' => $user->getFirstName(),
            'username' => $user->getUsername(),
        ]);

        $message = Message::fromResponse([
            'message_id' => random_int(1000, 9999),
            'date' => time(),
            'chat' => $privateChat->toJson(true),
            'from' => $user->toJson(true),
            'text' => $text,
        ]);

        return Update::fromResponse([
            'update_id' => random_int(10000, 99999),
            'message' => $message->toJson(true),
        ]);
    }
}
