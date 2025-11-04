<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Callbacks\CallbackRegistry;
use App\Commands\CommandRegistry;
use App\Http\Controllers\Controller;
use App\Messaging\MessengerAdapterInterface;
use App\Services\UserMessengerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

/**
 * Handles incoming webhooks from Telegram Bot API
 *
 * BotApi is injected via dependency injection for testability.
 * Register mock instances in tests to verify webhook handling logic.
 */
class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly BotApi $bot,
        private readonly CommandRegistry $commandRegistry,
        private readonly CallbackRegistry $callbackRegistry,
        private readonly MessengerAdapterInterface $messenger
    ) {}

    /**
     * Handle incoming webhook from Telegram
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Verify webhook secret
            if (! $this->verifyWebhookSecret($request)) {
                Log::warning('Invalid webhook secret', [
                    'ip' => $request->ip(),
                    'headers' => $request->headers->all(),
                ]);

                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Parse update from Telegram
            $update = Update::fromResponse($request->all());

            Log::info('Telegram update received', [
                'update_id' => $update->getUpdateId(),
                'type' => $this->getUpdateType($update),
            ]);

            // Handle the update
            $this->processUpdate($update);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    /**
     * Verify that the webhook request is from Telegram
     */
    private function verifyWebhookSecret(Request $request): bool
    {
        $secret = config('telegram.webhook.secret');

        if (empty($secret)) {
            return true; // Skip verification if no secret is set
        }

        $providedSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');

        return hash_equals($secret, $providedSecret ?? '');
    }

    /**
     * Process the Telegram update
     */
    private function processUpdate(Update $update): void
    {
        if ($message = $update->getMessage()) {
            $this->handleMessage($message);
        } elseif ($callbackQuery = $update->getCallbackQuery()) {
            $this->handleCallbackQuery($callbackQuery);
        }
    }

    /**
     * Handle incoming message
     */
    private function handleMessage(\TelegramBot\Api\Types\Message $message): void
    {
        $text = $message->getText();
        $chatId = $message->getChat()->getId();
        $chat = $message->getChat();

        Log::info('Message received', [
            'chat_id' => $chatId,
            'text' => $text,
            'from' => $message->getFrom()->getId(),
        ]);

        // Handle member status updates (user joined/left group)
        if ($this->isMemberStatusUpdate($message)) {
            $this->handleMemberStatusUpdate($message);
            return;
        }

        // Track group activity and auto-register user (if feature enabled and group message)
        if ($this->isGroupMessage($chat)) {
            $this->trackGroupActivity($chatId, $message);
        }

        // Ignore non-text messages (photos, stickers, documents, etc.)
        if ($text === null) {
            return;
        }

        // Handle commands
        if (str_starts_with($text, '/')) {
            $this->handleCommand($message);
            return;
        }

        // Regular message handling (for conversational flows)
        // TODO: Implement context-based message handling
    }

    /**
     * Handle bot commands - Routes through CommandRegistry
     */
    private function handleCommand(\TelegramBot\Api\Types\Message $message): void
    {
        // Parse webhook into platform-agnostic IncomingMessage
        $incomingMessage = $this->messenger->parseWebhook(request()->all());

        // Route to appropriate command handler via registry
        $this->commandRegistry->handle($incomingMessage);
    }

    /**
     * Handle callback queries - Routes through CallbackRegistry
     */
    private function handleCallbackQuery(\TelegramBot\Api\Types\CallbackQuery $callbackQuery): void
    {
        // Parse callback into platform-agnostic IncomingCallback
        $incomingCallback = $this->messenger->parseCallback(request()->all());

        // Route to appropriate callback handler via registry
        $this->callbackRegistry->handle($incomingCallback);
    }

    /**
     * Get update type for logging
     */
    private function getUpdateType(Update $update): string
    {
        return match (true) {
            $update->getMessage() !== null => 'message',
            $update->getEditedMessage() !== null => 'edited_message',
            $update->getCallbackQuery() !== null => 'callback_query',
            $update->getInlineQuery() !== null => 'inline_query',
            default => 'unknown',
        };
    }

    /**
     * Check if message is from a group (not private chat)
     */
    private function isGroupMessage(\TelegramBot\Api\Types\Chat $chat): bool
    {
        return in_array($chat->getType(), ['group', 'supergroup']);
    }

    /**
     * Check if message is a member status update (join/leave)
     */
    private function isMemberStatusUpdate(\TelegramBot\Api\Types\Message $message): bool
    {
        return $message->getNewChatMembers() !== null
            || $message->getLeftChatMember() !== null;
    }

    /**
     * Handle member status updates (user joined/left group)
     */
    private function handleMemberStatusUpdate(\TelegramBot\Api\Types\Message $message): void
    {
        $chatId = $message->getChat()->getId();

        // Find group by chat ID
        $group = \App\Models\Group::where('platform_chat_id', (string)$chatId)
            ->where('platform', 'telegram')
            ->first();

        if (!$group) {
            return; // Not a tracked group
        }

        // Handle new members
        if ($newMembers = $message->getNewChatMembers()) {
            foreach ($newMembers as $newMember) {
                // Skip if it's the bot itself
                if ($newMember->isBot()) {
                    continue;
                }

                $this->addUserToGroup($group, $newMember);
            }
        }

        // Handle member left
        if ($leftMember = $message->getLeftChatMember()) {
            // Skip if it's the bot itself
            if (!$leftMember->isBot()) {
                $this->removeUserFromGroup($group, $leftMember);
            }
        }
    }

    /**
     * Add user to group in database
     */
    private function addUserToGroup(\App\Models\Group $group, \TelegramBot\Api\Types\User $telegramUser): void
    {
        // Find or create user
        $user = UserMessengerService::findOrCreate(
            platform: 'telegram',
            platformUserId: (string)$telegramUser->getId(),
            userData: [
                'username' => $telegramUser->getUsername(),
                'first_name' => $telegramUser->getFirstName(),
                'last_name' => $telegramUser->getLastName(),
            ]
        );

        // Check if user is already in group
        if ($group->users()->where('user_id', $user->id)->exists()) {
            Log::info('User already in group', [
                'user_id' => $user->id,
                'group_id' => $group->id,
            ]);
            return;
        }

        // Check rejoin cooldown (prevents gaming system by leaving/rejoining for fresh points)
        $rejoinKey = "group_rejoin:{$group->id}:{$user->id}";
        $previousPoints = \Illuminate\Support\Facades\Cache::get($rejoinKey);

        if ($previousPoints !== null) {
            // User rejoined within 72 hours - restore previous points
            $pointsToAssign = $previousPoints;
            Log::info('User rejoined within cooldown period - restoring previous points', [
                'user_id' => $user->id,
                'group_id' => $group->id,
                'restored_points' => $pointsToAssign,
            ]);
        } else {
            // First time joining or cooldown expired - assign starting balance
            $pointsToAssign = $group->starting_balance ?? 1000;
        }

        // Add user to group
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => $pointsToAssign,
            'role' => 'participant',
            'last_activity_at' => now(),
        ]);

        Log::info('User added to group', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'group_id' => $group->id,
            'group_name' => $group->name ?? $group->platform_chat_title,
            'points_assigned' => $pointsToAssign,
        ]);
    }

    /**
     * Remove user from group in database
     */
    private function removeUserFromGroup(\App\Models\Group $group, \TelegramBot\Api\Types\User $telegramUser): void
    {
        // Find user via MessengerService
        $messengerService = \App\Models\MessengerService::findByPlatform('telegram', (string)$telegramUser->getId());

        if (!$messengerService) {
            return; // User not in our system
        }

        $user = $messengerService->user;

        // Get user's current points before removal
        $userInGroup = $group->users()->where('user_id', $user->id)->first();

        if ($userInGroup) {
            // Cache points for 72 hours to prevent gaming system by rejoining
            $rejoinKey = "group_rejoin:{$group->id}:{$user->id}";
            $points = $userInGroup->pivot->points;
            \Illuminate\Support\Facades\Cache::put($rejoinKey, $points, now()->addHours(72));

            Log::info('User points cached for rejoin protection', [
                'user_id' => $user->id,
                'group_id' => $group->id,
                'cached_points' => $points,
                'expires_at' => now()->addHours(72)->toDateTimeString(),
            ]);
        }

        // Remove from group
        $group->users()->detach($user->id);

        Log::info('User removed from group', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'group_id' => $group->id,
            'group_name' => $group->name ?? $group->platform_chat_title,
        ]);
    }

    /**
     * Track group activity for inactive group detection and auto-register users
     *
     * Uses Redis throttling to update DB only once per day per group.
     * This prevents excessive DB writes while maintaining accuracy for 14-day threshold.
     *
     * Also ensures user is registered in DB and attached to group.
     */
    private function trackGroupActivity(int $chatId, \TelegramBot\Api\Types\Message $message): void
    {
        // Feature flag check - exit early if disabled
        if (!config('features.activity_tracking', false)) {
            return;
        }

        // Find group by chat ID
        $group = \App\Models\Group::where('platform_chat_id', (string)$chatId)
            ->where('platform', 'telegram')
            ->first();

        if (!$group) {
            return; // Not a tracked group
        }

        // Auto-register user from message
        $from = $message->getFrom();
        if ($from && !$from->isBot()) {
            $user = UserMessengerService::findOrCreate(
                platform: 'telegram',
                platformUserId: (string)$from->getId(),
                userData: [
                    'username' => $from->getUsername(),
                    'first_name' => $from->getFirstName(),
                    'last_name' => $from->getLastName(),
                ]
            );

            // Ensure user is attached to group
            if (!$group->users()->where('user_id', $user->id)->exists()) {
                $group->users()->attach($user->id, [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'points' => $group->starting_balance ?? 1000,
                    'role' => 'participant',
                    'last_activity_at' => now(),
                ]);

                Log::info('User auto-registered to group from message', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'group_id' => $group->id,
                ]);
            } else {
                // Update last_activity_at for existing member
                $group->users()->updateExistingPivot($user->id, [
                    'last_activity_at' => now(),
                ]);
            }
        }

        // Redis throttling: only update group activity once per day
        $today = now()->toDateString();
        $cacheKey = "group_activity:{$group->id}:{$today}";

        // Check if we've already updated today
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return; // Already tracked today, skip DB write
        }

        // First message of the day for this group - update activity
        $group->update(['last_activity_at' => now()]);

        // Cache until end of day to prevent further updates today
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->endOfDay());

        Log::channel('operational')->info('activity_tracking.updated', [
            'group_id' => $group->id,
            'group_name' => $group->name ?? $group->platform_chat_title,
            'date' => $today,
        ]);
    }

}
