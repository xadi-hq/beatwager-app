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

        // Track group activity (if feature enabled)
        $this->trackGroupActivity($chatId);

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
            '/newevent' => $this->handleNewEventCommand($message),
            '/newchallenge' => $this->handleNewChallengeCommand($message),
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
        $welcomeMessage .= "I help you create and manage friendly wagers and events with your group.\n\n";
        $welcomeMessage .= "Available commands:\n";
        $welcomeMessage .= "/newwager - Create a new wager\n";
        $welcomeMessage .= "/newevent - Create a group event\n";
        $welcomeMessage .= "/newchallenge - Create a 1-on-1 challenge\n";
        $welcomeMessage .= "/join - Join an existing wager\n";
        $welcomeMessage .= "/mywagers - View your active wagers\n";
        $welcomeMessage .= "/balance - Check your points balance\n";
        $welcomeMessage .= "/leaderboard - View group leaderboard\n";
        $welcomeMessage .= "/help - Show this help message\n\n";
        $welcomeMessage .= "Let's get started! Use /newwager, /newevent, or /newchallenge to begin.";

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
        $helpMessage .= "• `/newevent` - Create a group event with attendance tracking\n";
        $helpMessage .= "• `/newchallenge` - Create a 1-on-1 challenge with points reward\n";
        $helpMessage .= "• `/mybets` - View your active wagers\n";
        $helpMessage .= "• `/balance` - Check your points balance\n";
        $helpMessage .= "• `/leaderboard` - View group rankings\n";
        $helpMessage .= "• `/help` - Show this help message\n\n";
        $helpMessage .= "*How it works:*\n";
        $helpMessage .= "1️⃣ Create a wager with `/newwager`\n";
        $helpMessage .= "2️⃣ Friends join with their predictions\n";
        $helpMessage .= "3️⃣ When the event happens, settle the wager\n";
        $helpMessage .= "4️⃣ Winners split the pot proportionally!\n\n";
        $helpMessage .= "*Events:*\n";
        $helpMessage .= "📅 Use `/newevent` to organize meetups with attendance bonuses\n";
        $helpMessage .= "✅ RSVP to events and earn points for showing up!\n\n";
        $helpMessage .= "*Challenges:*\n";
        $helpMessage .= "💪 Use `/newchallenge` to offer points for completing a task\n";
        $helpMessage .= "⚡ One person accepts and completes it to earn your points!\n\n";

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
     * Handle /newevent command
     */
    private function handleNewEventCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $chatId = $message->getChat()->getId();
        $from = $message->getFrom();
        $chat = $message->getChat();
        $chatType = $chat->getType();

        // Only allow in group chats
        if (!in_array($chatType, ['group', 'supergroup'])) {
            $this->bot->sendMessage(
                $chatId,
                "❌ Please use /newevent in a group chat where you want to create the event.\n\n" .
                "I need to know which group the event is for!"
            );
            return;
        }

        // Generate signed URL with all context needed for event creation
        $fullUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'events.create',
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

            $dmMessage = "📅 *Create a new event for {$chat->getTitle()}*\n\n";
            $dmMessage .= "Click the link to open the creation form:\n";
            $dmMessage .= $shortUrlFull . "\n\n";
            $dmMessage .= "⏱️ Link expires in 30 minutes";

            $this->bot->sendMessage(
                $from->getId(),
                $dmMessage,
                'Markdown'
            );

            // Confirm in group chat
            $groupMessage = "✅ {$username}, I've sent you the event creation link in our private chat!";
            $this->bot->sendMessage($chatId, $groupMessage);

        } catch (\Exception $e) {
            // If we can't send DM (user hasn't started bot), send link in group as fallback
            Log::warning('Could not send DM to user, falling back to group message', [
                'user_id' => $from->getId(),
                'error' => $e->getMessage(),
            ]);

            $fallbackMessage = "📅 *Create your event for this group!*\n\n";
            $fallbackMessage .= "Click the link to open the creation form:\n";
            $fallbackMessage .= $shortUrlFull . "\n\n";
            $fallbackMessage .= "⏱️ Link expires in 30 minutes\n\n";
            $fallbackMessage .= "💡 Tip: Start a private chat with me first using /start so I can send you links privately!";

            $this->bot->sendMessage($chatId, $fallbackMessage, 'Markdown');
        }
    }

    /**
     * Handle /newchallenge command
     */
    private function handleNewChallengeCommand(\TelegramBot\Api\Types\Message $message): void
    {
        $chatId = $message->getChat()->getId();
        $from = $message->getFrom();
        $chat = $message->getChat();
        $chatType = $chat->getType();
        
        // Only allow in group chats
        if (!in_array($chatType, ['group', 'supergroup'])) {
            $this->bot->sendMessage(
                $chatId,
                "❌ Please use /newchallenge in a group chat where you want to create the challenge.\n\n" .
                "I need to know which group the challenge is for!"
            );
            return;
        }

        // Generate signed URL with all context needed for challenge creation
        $fullUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'challenges.create',
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

            $dmMessage = "💪 *Create a new challenge for {$chat->getTitle()}*\n\n";
            $dmMessage .= "Click the link to open the creation form:\n";
            $dmMessage .= $shortUrlFull . "\n\n";
            $dmMessage .= "⏱️ Link expires in 30 minutes";

            $this->bot->sendMessage(
                $from->getId(),
                $dmMessage,
                'Markdown'
            );

            // Confirm in group chat
            $groupMessage = "✅ {$username}, I've sent you the challenge creation link in our private chat!";
            $this->bot->sendMessage($chatId, $groupMessage);

        } catch (\Exception $e) {
            // If we can't send DM (user hasn't started bot), send link in group as fallback
            Log::warning('Could not send DM to user, falling back to group message', [
                'user_id' => $from->getId(),
                'error' => $e->getMessage(),
            ]);

            $fallbackMessage = "💪 *Create your challenge for this group!*\n\n";
            $fallbackMessage .= "Click the link to open the creation form:\n";
            $fallbackMessage .= $shortUrlFull . "\n\n";
            $fallbackMessage .= "⏱️ Link expires in 30 minutes\n\n";
            $fallbackMessage .= "💡 Tip: Start a private chat with me first using /start so I can send you links privately!";

            $this->bot->sendMessage($chatId, $fallbackMessage, 'Markdown');
        }
    }

    /**

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
            ->orderBy('betting_closes_at', 'asc')
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
            // Filter out wagers with past deadlines (should have been settled)
            $futureWagers = $activeWagers->filter(fn($w) => $w->betting_closes_at->isFuture());

            // Separate wagers into closing soon (≤7 days) and others
            $closingSoon = $futureWagers->filter(fn($w) => $w->betting_closes_at->diffInDays(now()) <= 7);
            $later = $futureWagers->filter(fn($w) => $w->betting_closes_at->diffInDays(now()) > 7);

            // Show closing soon wagers
            if ($closingSoon->isNotEmpty()) {
                $message .= "⏰ *Closing Soon:*\n";
                foreach ($closingSoon as $i => $wager) {
                    $deadline = $wager->betting_closes_at;
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

            // Show later wagers
            if ($later->isNotEmpty()) {
                $message .= "📅 *Coming Up:*\n";
                foreach ($later as $i => $wager) {
                    $deadline = $wager->betting_closes_at;
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

            // If there are past wagers, show count for awareness
            $pastCount = $activeWagers->count() - $futureWagers->count();
            if ($pastCount > 0) {
                $message .= "⚠️ {$pastCount} wager(s) past deadline - awaiting settlement\n\n";
            }
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
            // Parse callback data format: "wager:{wager_id}:{answer}", "view:{wager_id}", "event_rsvp:{event_id}:{response}", or "challenge_{action}:{challenge_id}"
            $parts = explode(':', $data);

            if (count($parts) < 2) {
                throw new \Exception('Invalid callback data format');
            }

            $action = $parts[0];

            // Handle event RSVP buttons
            if ($action === 'event_rsvp') {
                $this->handleEventRsvpCallback($callbackQuery, $parts);
                return;
            }

            // Handle challenge buttons
            if ($action === 'challenge_accept') {
                $this->handleChallengeAcceptCallback($callbackQuery, $parts);
                return;
            }
            
            if ($action === 'challenge_view') {
                $this->handleChallengeViewCallback($callbackQuery, $parts);
                return;
            }

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
            if ($wager->betting_closes_at < now()) {
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
            $entry = $wagerService->placeWager($wager, $user, $answer, $wager->stake_amount);

            // Send success message (without revealing answer)
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '✅ Wager placed successfully!', 'show_alert' => false]
            );

            // Dispatch event for async LLM-powered announcement with engagement triggers
            \App\Events\WagerJoined::dispatch($wager, $entry, $user);

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
     * Handle event RSVP callback from inline buttons
     */
    private function handleEventRsvpCallback(\TelegramBot\Api\Types\CallbackQuery $callbackQuery, array $parts): void
    {
        // Callback data format: "event_rsvp:{event_id}:{response}"
        if (count($parts) !== 3) {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Invalid RSVP format', 'show_alert' => true]
            );
            return;
        }

        $eventId = $parts[1];
        $response = $parts[2]; // going, maybe, not_going

        // Validate response
        if (!in_array($response, ['going', 'maybe', 'not_going'])) {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Invalid RSVP response', 'show_alert' => true]
            );
            return;
        }

        // Find the event
        $event = \App\Models\GroupEvent::find($eventId);
        if (!$event) {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Event not found', 'show_alert' => true]
            );
            return;
        }

        // Check if event is still upcoming
        if ($event->status !== 'upcoming') {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ This event is no longer active', 'show_alert' => true]
            );
            return;
        }

        // Check if RSVP deadline has passed (if set)
        if ($event->rsvp_deadline && $event->rsvp_deadline < now()) {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ RSVP deadline has passed', 'show_alert' => true]
            );
            return;
        }

        // Get or create user from Telegram
        $userId = $callbackQuery->getFrom()->getId();
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
        $group = $event->group;

        // Ensure user is in the group
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            $group->users()->attach($user->id, [
                'id' => \Illuminate\Support\Str::uuid(),
                'points' => $group->starting_balance ?? 1000,
                'role' => 'participant',
            ]);
        }

        // Record RSVP using EventService (returns previous response for change detection)
        $eventService = app(\App\Services\EventService::class);
        $rsvpResult = $eventService->recordRsvp($event, $user, $response);
        $previousResponse = $rsvpResult['previous_response'];

        // Debug logging
        \Log::info('RSVP Change Debug', [
            'event' => $event->name,
            'user' => $user->name,
            'new_response' => $response,
            'previous_response' => $previousResponse,
            'was_changed' => $rsvpResult['was_changed'],
        ]);

        // Map response to emoji and text
        $responseMap = [
            'going' => ['emoji' => '✅', 'text' => 'Going'],
            'maybe' => ['emoji' => '🤔', 'text' => 'Maybe'],
            'not_going' => ['emoji' => '❌', 'text' => "Can't Make It"],
        ];

        $responseInfo = $responseMap[$response];

        // Send success message
        $this->bot->answerCallbackQuery(
            $callbackQuery->getId(),
            ['text' => "{$responseInfo['emoji']} RSVP updated: {$responseInfo['text']}", 'show_alert' => false]
        );

        // Dispatch event for async LLM-powered announcement with personality (includes change detection)
        \App\Events\EventRsvpUpdated::dispatch($event, $user, $response, $previousResponse);
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

    /**
     * Track group activity for inactive group detection
     *
     * Uses Redis throttling to update DB only once per day per group.
     * This prevents excessive DB writes while maintaining accuracy for 14-day threshold.
     */
    private function trackGroupActivity(int $chatId): void
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

        // Redis throttling: only update once per day per group
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

    /**
     * Handle challenge accept callback from inline buttons
     */
    private function handleChallengeAcceptCallback(\TelegramBot\Api\Types\CallbackQuery $callbackQuery, array $parts): void
    {
        // Callback data format: "challenge_accept:{challenge_id}"
        if (count($parts) !== 2) {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Invalid challenge format', 'show_alert' => true]
            );
            return;
        }

        $challengeId = $parts[1];

        // Find the challenge
        $challenge = \App\Models\Challenge::find($challengeId);
        if (!$challenge) {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Challenge not found', 'show_alert' => true]
            );
            return;
        }

        // Check if challenge is still open
        if (!$challenge->canBeAccepted()) {
            $message = $challenge->isAcceptanceExpired() 
                ? '❌ Challenge acceptance deadline has passed'
                : '❌ This challenge is no longer available';
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => $message, 'show_alert' => true]
            );
            return;
        }

        // Get or create user from Telegram
        $userId = $callbackQuery->getFrom()->getId();
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
        $group = $challenge->group;

        // Ensure user is in the group
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            $group->users()->attach($user->id, [
                'id' => \Illuminate\Support\Str::uuid(),
                'points' => $group->starting_balance ?? 1000,
                'role' => 'participant',
            ]);
        }

        try {
            // Accept challenge using ChallengeService
            $challengeService = app(\App\Services\ChallengeService::class);
            $acceptedChallenge = $challengeService->acceptChallenge($challenge, $user);

            // Send success message
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '💪 Challenge accepted! Good luck!', 'show_alert' => false]
            );

            // TODO: Dispatch event for challenge accepted announcement
            // \App\Events\ChallengeAccepted::dispatch($acceptedChallenge, $user);

        } catch (\Exception $e) {
            Log::error('Error accepting challenge', [
                'error' => $e->getMessage(),
                'challenge_id' => $challengeId,
                'user_id' => $userId,
            ]);

            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Error: ' . $e->getMessage(), 'show_alert' => true]
            );
        }
    }

    /**
     * Handle challenge view callback from inline buttons
     */
    private function handleChallengeViewCallback(\TelegramBot\Api\Types\CallbackQuery $callbackQuery, array $parts): void
    {
        // Callback data format: "challenge_view:{challenge_id}"
        if (count($parts) !== 2) {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Invalid challenge format', 'show_alert' => true]
            );
            return;
        }

        $challengeId = $parts[1];

        // Find the challenge
        $challenge = \App\Models\Challenge::find($challengeId);
        if (!$challenge) {
            $this->bot->answerCallbackQuery(
                $callbackQuery->getId(),
                ['text' => '❌ Challenge not found', 'show_alert' => true]
            );
            return;
        }

        $userId = $callbackQuery->getFrom()->getId();

        // Generate signed URL with encrypted user ID
        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'challenges.show',
            now()->addDays(30),
            [
                'challenge' => $challengeId,
                'u' => encrypt('telegram:' . $userId)
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
            ['text' => '🎯 Opening challenge details...']
        );

        // Send short URL as direct message to user
        $dmMessage = "🎯 *View Challenge Details*\n\n";
        $dmMessage .= "💪 Challenge: {$challenge->description}\n\n";
        $dmMessage .= "Click to view details and status:\n";
        $dmMessage .= $shortUrlFull;

        try {
            $this->bot->sendMessage(
                $userId,
                $dmMessage,
                'Markdown'
            );
        } catch (\Exception $e) {
            // If DM fails, could send to group chat as fallback
            Log::warning('Failed to send challenge view DM', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
