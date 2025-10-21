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

        Log::info('Message received', [
            'chat_id' => $chatId,
            'text' => $text,
            'from' => $message->getFrom()->getId(),
        ]);

        // Track group activity (if feature enabled)
        $this->trackGroupActivity($chatId);

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

}
