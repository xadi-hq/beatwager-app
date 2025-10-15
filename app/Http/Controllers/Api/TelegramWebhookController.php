<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserMessengerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

/**
 * Handles incoming webhooks from Telegram Bot API
 *
 * NOTE: This controller is not unit tested due to direct BotApi instantiation.
 * Webhook functionality should be verified through:
 * - Manual testing with a Telegram test bot
 * - Monitoring production logs for webhook errors
 * - E2E tests if webhook testing becomes critical
 *
 * TODO: Consider refactoring to use dependency injection for BotApi to enable mocking
 */
class TelegramWebhookController extends Controller
{
    private BotApi $bot;

    public function __construct()
    {
        $this->bot = new BotApi(config('telegram.bot_token'));
    }

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

        Log::info('Message received', [
            'chat_id' => $chatId,
            'text' => $text,
            'from' => $message->getFrom()->getId(),
        ]);

        // Handle commands
        if (str_starts_with($text, '/')) {
            $this->handleCommand($message);
            return;
        }

        // Regular message handling (for conversational flows)
        // TODO: Implement context-based message handling
    }

    /**
     * Handle bot commands
     */
    private function handleCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $text = $message->getText();
        $command = strtok($text, ' ');

        match ($command) {
            '/start' => $this->handleStartCommand($message),
            '/help' => $this->handleHelpCommand($message),
            '/newwager' => $this->handleNewWagerCommand($message),
            '/join' => $this->handleJoinCommand($message),
            '/mywagers', '/mybets' => $this->handleMyWagersCommand($message),
            '/balance', '/mybalance' => $this->handleBalanceCommand($message),
            '/leaderboard' => $this->handleLeaderboardCommand($message),
            default => $this->handleUnknownCommand($message),
        };
    }

    /**
     * Handle /start command
     */
    private function handleStartCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $chatId = $message->getChat()->getId();
        $firstName = $message->getFrom()->getFirstName();

        $welcomeMessage = "👋 Welcome to BeatWager, {$firstName}!\n\n";
        $welcomeMessage .= "I help you create and manage friendly wagers with your group.\n\n";
        $welcomeMessage .= "Available commands:\n";
        $welcomeMessage .= "/newwager - Create a new wager\n";
        $welcomeMessage .= "/join - Join an existing wager\n";
        $welcomeMessage .= "/mywagers - View your active wagers\n";
        $welcomeMessage .= "/balance - Check your points balance\n";
        $welcomeMessage .= "/leaderboard - View group leaderboard\n";
        $welcomeMessage .= "/help - Show this help message\n\n";
        $welcomeMessage .= "Let's get started! Use /newwager to create your first wager.";

        $this->bot->sendMessage($chatId, $welcomeMessage);
    }

    /**
     * Handle /help command
     */
    private function handleHelpCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $from = $message->getFrom();
        $userId = $from->getId();
        $chat = $message->getChat();
        $chatId = $chat->getId();
        $chatType = $chat->getType();

        // Build help message
        $helpMessage = "📖 *BeatWager Help*\n\n";
        $helpMessage .= "*Available Commands:*\n\n";
        $helpMessage .= "• `/newwager` - Create a new wager in a group\n";
        $helpMessage .= "• `/mybets` - View your active wagers\n";
        $helpMessage .= "• `/balance` - Check your points balance\n";
        $helpMessage .= "• `/leaderboard` - View group rankings\n";
        $helpMessage .= "• `/help` - Show this help message\n\n";
        $helpMessage .= "*How it works:*\n";
        $helpMessage .= "1️⃣ Create a wager with `/newwager`\n";
        $helpMessage .= "2️⃣ Friends join with their predictions\n";
        $helpMessage .= "3️⃣ When the event happens, settle the wager\n";
        $helpMessage .= "4️⃣ Winners split the pot proportionally!\n\n";

        // Create short URL to help page
        $shortCode = \App\Models\ShortUrl::generateUniqueCode(6);
        \App\Models\ShortUrl::create([
            'code' => $shortCode,
            'target_url' => config('app.url') . '/help',
            'expires_at' => now()->addMonth(),
        ]);
        $shortUrl = url('/l/' . $shortCode);

        $helpMessage .= "📚 Full documentation: {$shortUrl}";

        // Send as DM if possible
        try {
            $this->bot->sendMessage($userId, $helpMessage, 'Markdown');

            // If command was in group, acknowledge
            if (in_array($chatType, ['group', 'supergroup'])) {
                $this->bot->sendMessage($chatId, "✅ Help sent to your DM, @{$from->getUsername()}!");
            }
        } catch (\Exception $e) {
            // Fallback: send in chat if DM fails
            \Log::warning('Failed to send help DM, sending to chat', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            $this->bot->sendMessage($chatId, $helpMessage, 'Markdown');
        }
    }

    /**
     * Handle /newwager command
     */
    private function handleNewWagerCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $chatId = $message->getChat()->getId();
        $from = $message->getFrom();
        $chat = $message->getChat();
        $chatType = $chat->getType();
        
        // Only allow in group chats
        if (!in_array($chatType, ['group', 'supergroup'])) {
            $this->bot->sendMessage(
                $chatId,
                "❌ Please use /newwager in a group chat where you want to create the wager.\n\n" .
                "I need to know which group the wager is for!"
            );
            return;
        }

        // Generate signed URL with all context needed for wager creation
        $fullUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'wager.create',
            now()->addMinutes(30),
            [
                'u' => encrypt('telegram:' . $from->getId()),
                'username' => $from->getUsername(),
                'first_name' => $from->getFirstName(),
                'last_name' => $from->getLastName(),
                'chat_id' => $chat->getId(),
                'chat_type' => $chatType,
                'chat_title' => $chat->getTitle(),
            ]
        );

        // Create short URL for cleaner message
        $shortCode = \App\Models\ShortUrl::generateUniqueCode(6);
        $shortUrl = \App\Models\ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $fullUrl,
            'expires_at' => now()->addMinutes(30),
        ]);
        $shortUrlFull = url('/l/' . $shortCode);

        // Send link to user's private chat
        try {
            $username = $from->getUsername() ? '@' . $from->getUsername() : $from->getFirstName();

            $dmMessage = "🎲 *Create a new wager for {$chat->getTitle()}*\n\n";
            $dmMessage .= "Click the link to open the creation form:\n";
            $dmMessage .= $shortUrlFull . "\n\n";
            $dmMessage .= "⏱️ Link expires in 30 minutes";

            $this->bot->sendMessage(
                $from->getId(),
                $dmMessage,
                'Markdown'
            );

            // Confirm in group chat
            $groupMessage = "✅ {$username}, I've sent you the wager creation link in our private chat!";
            $this->bot->sendMessage($chatId, $groupMessage);

        } catch (\Exception $e) {
            // If we can't send DM (user hasn't started bot), send link in group as fallback
            Log::warning('Could not send DM to user, falling back to group message', [
                'user_id' => $from->getId(),
                'error' => $e->getMessage(),
            ]);

            $fallbackMessage = "🎲 *Create your wager for this group!*\n\n";
            $fallbackMessage .= "Click the link to open the creation form:\n";
            $fallbackMessage .= $shortUrlFull . "\n\n";
            $fallbackMessage .= "⏱️ Link expires in 30 minutes\n\n";
            $fallbackMessage .= "💡 Tip: Start a private chat with me first using /start so I can send you links privately!";

            $this->bot->sendMessage($chatId, $fallbackMessage, 'Markdown');
        }
    }









    /**
     * Handle /join command
     */
    private function handleJoinCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $chatId = $message->getChat()->getId();

        $this->bot->sendMessage(
            $chatId,
            '✋ Join wager coming soon! This will let you join an existing wager.'
        );
    }

    /**
     * Handle /mywagers (/mybets) command
     */
    private function handleMyWagersCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $from = $message->getFrom();
        $userId = $from->getId();

        // Find or create user with Telegram messenger service
        $user = UserMessengerService::findOrCreate(
            platform: 'telegram',
            platformUserId: (string) $userId,
            userData: [
                'username' => $from->getUsername(),
                'first_name' => $from->getFirstName(),
                'last_name' => $from->getLastName(),
            ]
        );

        // Get user's active wagers (created or joined), sorted by deadline
        $activeWagers = \App\Models\Wager::where('status', 'open')
            ->where(function($query) use ($user) {
                $query->where('creator_id', $user->id)
                      ->orWhereHas('entries', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->orderBy('deadline', 'asc')
            ->limit(5)
            ->get();

        // Generate signed URL for dashboard
        $fullUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'dashboard.me',
            now()->addDay(),
            [
                'u' => encrypt('telegram:' . $userId),
                'focus' => 'wagers', // Which section to highlight
            ]
        );

        // Create short URL
        $shortCode = \App\Models\ShortUrl::generateUniqueCode(6);
        \App\Models\ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $fullUrl,
            'expires_at' => now()->addDay(),
        ]);
        $shortUrl = url('/l/' . $shortCode);

        // Format message
        $count = $activeWagers->count();
        $message = "📊 *Your Active Wagers* ({$count})\n\n";

        if ($count === 0) {
            $message .= "No active wagers yet.\nUse /newwager in a group to create one!\n\n";
        } else {
            $message .= "⏰ *Closing Soon:*\n";
            foreach ($activeWagers as $i => $wager) {
                $deadline = $wager->deadline;
                $diff = now()->diff($deadline);

                if ($diff->days > 0) {
                    $timeLeft = $diff->days . 'd ' . $diff->h . 'h';
                } elseif ($diff->h > 0) {
                    $timeLeft = $diff->h . 'h ' . $diff->i . 'm';
                } else {
                    $timeLeft = $diff->i . 'm';
                }

                $title = strlen($wager->title) > 40 ? substr($wager->title, 0, 37) . '...' : $wager->title;
                $message .= ($i + 1) . ". \"{$title}\" - {$timeLeft}\n";
            }
            $message .= "\n";
        }

        $message .= "👉 View all: {$shortUrl}";

        try {
            $this->bot->sendMessage($userId, $message, 'Markdown');
        } catch (\Exception $e) {
            // If DM fails, tell user to start bot first
            $this->bot->sendMessage(
                $message->getChat()->getId(),
                "⚠️ I need to send you a private message, but you haven't started a chat with me yet.\n\n" .
                "Click here to start: @" . $this->bot->getMe()->getUsername() . "\n" .
                "Then try /mybets again!"
            );
        }
    }

    /**
     * Escape special characters for Telegram Markdown
     */
    private function escapeMarkdown(string $text): string
    {
        return str_replace(
            ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)', '\\~', '\\`', '\\>', '\\#', '\\+', '\\-', '\\=', '\\|', '\\{', '\\}', '\\.', '\\!'],
            $text
        );
    }

    /**
     * Handle /balance command
     */
    private function handleBalanceCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $from = $message->getFrom();
        $userId = $from->getId();
        $chat = $message->getChat();
        $chatId = $chat->getId();
        $chatType = $chat->getType();

        // Find or create user with Telegram messenger service
        $user = UserMessengerService::findOrCreate(
            platform: 'telegram',
            platformUserId: (string) $userId,
            userData: [
                'username' => $from->getUsername(),
                'first_name' => $from->getFirstName(),
                'last_name' => $from->getLastName(),
            ]
        );

        // Determine context: if in group, show that group's balance; if DM, show all groups
        $contextGroup = null;
        $balanceText = '';

        if (in_array($chatType, ['group', 'supergroup'])) {
            // Find group by platform chat ID
            $contextGroup = \App\Models\Group::where('platform', 'telegram')
                ->where('platform_chat_id', (string) $chatId)
                ->first();

            if (!$contextGroup) {
                $this->bot->sendMessage($chatId, '❌ This group is not registered in BeatWager.');
                return;
            }

            // Get user's balance in this group
            $groupUser = $contextGroup->users()->where('users.id', $user->id)->first();

            if (!$groupUser) {
                $this->bot->sendMessage($chatId, '❌ You are not a member of this group.');
                return;
            }

            $balance = $groupUser->pivot->points;
            $groupName = $this->escapeMarkdown($contextGroup->name);
            $balanceText = "💰 *Your Balance in {$groupName}*\n\n";
            $balanceText .= "Current: *{$balance} points*\n\n";
        } else {
            // DM context - show summary across all groups
            $groups = $user->groups()->get();

            if ($groups->isEmpty()) {
                $this->bot->sendMessage($userId, '❌ You are not a member of any groups yet.');
                return;
            }

            $balanceText = "💰 *Your Balances Across Groups*\n\n";
            foreach ($groups as $group) {
                $balance = $group->pivot->points;
                $groupName = $this->escapeMarkdown($group->name);
                $balanceText .= "• {$groupName}: *{$balance} pts*\n";
            }
            $balanceText .= "\n";
        }

        // Get recent transactions
        $query = \App\Models\Transaction::where('user_id', $user->id);

        if ($contextGroup) {
            $query->where('group_id', $contextGroup->id);
        }

        $recentTransactions = $query->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        if ($recentTransactions->isNotEmpty()) {
            $balanceText .= "📊 *Recent Activity:*\n";
            foreach ($recentTransactions as $tx) {
                $sign = $tx->amount >= 0 ? '+' : '';
                $emoji = $tx->amount >= 0 ? '📈' : '📉';
                $rawDesc = $tx->description ?: $tx->type;
                // Replace underscores with spaces and title case
                $formatted = str_replace('_', ' ', $rawDesc);
                $desc = $this->escapeMarkdown(ucwords($formatted));
                $balanceText .= "{$emoji} {$sign}{$tx->amount} pts — {$desc}\n";
            }
            $balanceText .= "\n";
        }

        // Generate signed URL for dashboard with transactions focus
        $params = [
            'u' => encrypt('telegram:' . $userId),
            'focus' => 'transactions',
        ];
        if ($contextGroup) {
            $params['group_id'] = $contextGroup->id;
        }

        $fullUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'dashboard.me',
            now()->addDay(),
            $params
        );

        // Create short URL
        $shortCode = \App\Models\ShortUrl::generateUniqueCode(6);
        \App\Models\ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $fullUrl,
            'expires_at' => now()->addDay(),
        ]);
        $shortUrl = url('/l/' . $shortCode);

        $balanceText .= "👉 View full history: {$shortUrl}";

        // Send DM to user
        try {
            $this->bot->sendMessage($userId, $balanceText, 'Markdown');

            // If command was in group, acknowledge
            if (in_array($chatType, ['group', 'supergroup'])) {
                $this->bot->sendMessage($chatId, "✅ Balance sent to your DM, @{$from->getUsername()}!");
            }
        } catch (\Exception $e) {
            // Fallback if user hasn't started bot
            \Log::error('Failed to send balance DM', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            if (in_array($chatType, ['group', 'supergroup'])) {
                $this->bot->sendMessage(
                    $chatId,
                    "❌ I couldn't send you a DM. Please start a conversation with @" . config('telegram.bot_username') . " first."
                );
            }
        }
    }

    /**
     * Handle /leaderboard command
     */
    private function handleLeaderboardCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $chatId = $message->getChat()->getId();

        $this->bot->sendMessage(
            $chatId,
            '🏆 Leaderboard coming soon! This will show the top players in your group.'
        );
    }

    /**
     * Handle callback query from inline buttons
     */
    private function handleCallbackQuery(\TelegramBot\Api\Types\CallbackQuery $callbackQuery): void
    {
        $data = $callbackQuery->getData();
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();

        Log::info('Callback query received', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'data' => $data,
        ]);

        try {
            // Parse callback data format: "wager:{wager_id}:{answer}" or "view:{wager_id}"
            $parts = explode(':', $data);
            
            if (count($parts) < 2) {
                throw new \Exception('Invalid callback data format');
            }

            $action = $parts[0];
            $wagerId = $parts[1];

            // Handle "View Progress" button
            if ($action === 'view') {
                $wager = \App\Models\Wager::find($wagerId);
                if (!$wager) {
                    $this->bot->answerCallbackQuery(
                        $callbackQuery->getId(),
                        ['text' => '❌ Wager not found', 'show_alert' => true]
                    );
                    return;
                }

                // Generate signed URL with encrypted user ID
                $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'wager.show',
                    now()->addDays(30),
                    [
                        'wager' => $wagerId,
                        'u' => encrypt($userId)
                    ]
                );

                // Create short URL
                $shortCode = \App\Models\ShortUrl::generateUniqueCode(6);
                $shortUrl = \App\Models\ShortUrl::create([
                    'code' => $shortCode,
                    'target_url' => $signedUrl,
                    'expires_at' => now()->addDays(30),
                ]);

                // Build short URL
                $shortUrlFull = url('/l/' . $shortCode);

                // Answer callback query
                $this->bot->answerCallbackQuery(
                    $callbackQuery->getId(),
                    ['text' => '📊 Opening wager details...']
                );

                // Send short URL as plain text direct message to user
                $dmMessage = "📊 *View Wager Progress*\n\n";
                $dmMessage .= "🎯 Wager: {$wager->title}\n\n";
                $dmMessage .= "Click to view details, stats, and settlement:\n";
                $dmMessage .= $shortUrlFull;

                $this->bot->sendMessage(
                    $userId,
                    $dmMessage,
                    'Markdown'
                );
                return;
            }

            // Handle "Join Wager" buttons (existing logic)
            if ($action !== 'wager' || count($parts) !== 3) {
                throw new \Exception('Invalid callback data format');
            }

            $answer = $parts[2];

            // Find the wager
            $wager = \App\Models\Wager::find($wagerId);
            if (!$wager) {
                $this->bot->answerCallbackQuery(
                    $callbackQuery->getId(),
                    ['text' => '❌ Wager not found', 'show_alert' => true]
                );
                return;
            }

            // Check if wager is still open
            if ($wager->status !== 'open') {
                $this->bot->answerCallbackQuery(
                    $callbackQuery->getId(),
                    ['text' => '❌ This wager is no longer open', 'show_alert' => true]
                );
                return;
            }

            // Check if deadline has passed
            if ($wager->deadline < now()) {
                $this->bot->answerCallbackQuery(
                    $callbackQuery->getId(),
                    ['text' => '❌ Deadline has passed', 'show_alert' => true]
                );
                return;
            }

            // Get or create user from Telegram with messenger service
            $user = UserMessengerService::findOrCreate(
                platform: 'telegram',
                platformUserId: (string) $userId,
                userData: [
                    'username' => $callbackQuery->getFrom()->getUsername(),
                    'first_name' => $callbackQuery->getFrom()->getFirstName(),
                    'last_name' => $callbackQuery->getFrom()->getLastName(),
                ]
            );

            // Get the group
            $group = $wager->group;

            // Ensure user is in the group
            if (!$group->users()->where('user_id', $user->id)->exists()) {
                $group->users()->attach($user->id, [
                    'id' => \Illuminate\Support\Str::uuid(),
                    'points' => $group->starting_balance ?? 1000,
                    'role' => 'participant',
                ]);
            }

            // Check if user already joined this wager
            $existingEntry = \App\Models\WagerEntry::where('wager_id', $wager->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingEntry) {
                $this->bot->answerCallbackQuery(
                    $callbackQuery->getId(),
                    ['text' => '⚠️ You already joined this wager', 'show_alert' => true]
                );
                return;
            }

            // Place the wager using WagerService
            $wagerService = app(\App\Services\WagerService::class);
            $wagerService->placeWager($wager, $user, $answer, $wager->stake_amount);

            // Send success message (without revealing answer)
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '✅ Wager placed successfully!', 'show_alert' => false]
            );

            // Send confirmation message to the chat (without revealing answer)
            $this->bot->sendMessage(
                $chatId,
                "✅ {$user->name} joined the wager: \"{$wager->title}\""
            );

        } catch (\App\Exceptions\InsufficientPointsException $e) {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Insufficient points: ' . $e->getMessage(), 'show_alert' => true]
            );
        } catch (\Exception $e) {
            Log::error('Error handling callback query', [
                'error' => $e->getMessage(),
                'data' => $data,
                'user_id' => $userId,
            ]);

            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Error: ' . $e->getMessage(), 'show_alert' => true]
            );
        }
    }

    /**
     * Handle unknown commands
     */
    private function handleUnknownCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $chatId = $message->getChat()->getId();

        $this->bot->sendMessage(
            $chatId,
            "❓ Unknown command. Use /help to see available commands."
        );
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
}
