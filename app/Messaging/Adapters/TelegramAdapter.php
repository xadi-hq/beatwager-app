<?php

declare(strict_types=1);

namespace App\Messaging\Adapters;

use App\Messaging\MessengerAdapterInterface;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\MessageType;
use App\Messaging\DTOs\OutgoingMessage;
use App\DTOs\Button;
use App\DTOs\ButtonAction;
use Illuminate\Support\Facades\URL;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Telegram platform adapter
 *
 * Translates Telegram Bot API interactions into platform-agnostic messaging
 */
class TelegramAdapter implements MessengerAdapterInterface
{
    public function __construct(
        private readonly BotApi $bot
    ) {}

    public function getPlatform(): string
    {
        return 'telegram';
    }

    public function sendMessage(OutgoingMessage $message): void
    {
        $params = [
            'chat_id' => $message->chatId,
            'text' => $message->text,
        ];

        // Add parse mode if specified
        if ($message->parseMode !== null) {
            $params['parse_mode'] = $message->parseMode;
        }

        // Add inline buttons if specified
        if ($message->buttons !== null) {
            $params['reply_markup'] = $this->buildInlineKeyboard($message->buttons);
        }

        // Add reply keyboard if specified
        if ($message->keyboard !== null) {
            $params['reply_markup'] = $this->buildReplyKeyboard($message->keyboard);
        }

        // Disable web page preview if requested
        if ($message->disablePreview) {
            $params['disable_web_page_preview'] = true;
        }

        // Merge any additional metadata
        $params = array_merge($params, $message->metadata);

        $this->bot->sendMessage(
            $params['chat_id'],
            $params['text'],
            $params['parse_mode'] ?? null,
            $params['disable_web_page_preview'] ?? false,
            null, // reply_to_message_id
            $params['reply_markup'] ?? null
        );
    }

    public function sendDirectMessage(string $userId, OutgoingMessage $message): void
    {
        // For Telegram, sending a DM is the same as sending a message to user's chat
        $dmMessage = new OutgoingMessage(
            chatId: $userId,
            text: $message->text,
            parseMode: $message->parseMode,
            buttons: $message->buttons,
            keyboard: $message->keyboard,
            disablePreview: $message->disablePreview,
            metadata: $message->metadata,
        );

        $this->sendMessage($dmMessage);
    }

    public function answerCallback(string $callbackId, string $text, bool $showAlert = false): void
    {
        $this->bot->answerCallbackQuery(
            $callbackId,
            $text,
            $showAlert
        );
    }

    /**
     * Send a photo message to a chat.
     *
     * @param string $chatId The chat ID to send to
     * @param string $photoUrl URL of the photo to send
     * @param string|null $caption Optional caption for the photo
     * @param string|null $parseMode Parse mode for caption (HTML, Markdown, etc.)
     * @param array<int, array<int, \App\DTOs\Button|array<string, mixed>>>|null $buttons Optional inline keyboard buttons
     */
    public function sendPhoto(
        string $chatId,
        string $photoUrl,
        ?string $caption = null,
        ?string $parseMode = 'HTML',
        ?array $buttons = null
    ): void {
        $params = [
            'chat_id' => $chatId,
            'photo' => $photoUrl,
        ];

        if ($caption !== null) {
            $params['caption'] = $caption;
        }

        if ($parseMode !== null) {
            $params['parse_mode'] = $parseMode;
        }

        if ($buttons !== null) {
            $params['reply_markup'] = $this->buildInlineKeyboard($buttons);
        }

        $this->bot->call('sendPhoto', $params);
    }

    public function createAuthenticatedUrl(string $route, array $params, int $expiryMinutes = 30): string
    {
        return URL::temporarySignedRoute(
            $route,
            now()->addMinutes($expiryMinutes),
            $params
        );
    }

    public function verifyWebhook(array $payload, array $headers): bool
    {
        $secret = config('telegram.webhook.secret');

        if (empty($secret)) {
            return true; // Skip verification if no secret is set
        }

        $providedSecret = $headers['x-telegram-bot-api-secret-token'] ?? null;

        return hash_equals($secret, $providedSecret ?? '');
    }

    public function parseWebhook(array $payload): IncomingMessage
    {
        $update = Update::fromResponse($payload);

        // Handle regular message
        if ($message = $update->getMessage()) {
            return $this->parseMessage($message);
        }

        // Handle callback query (button clicks)
        if ($callbackQuery = $update->getCallbackQuery()) {
            return $this->parseCallbackQuery($callbackQuery);
        }

        // Fallback for other update types
        throw new \RuntimeException('Unsupported Telegram update type');
    }

    /**
     * Parse Telegram message into IncomingMessage
     */
    private function parseMessage(Message $message): IncomingMessage
    {
        $text = $message->getText() ?? '';
        $from = $message->getFrom();
        $chat = $message->getChat();

        // Determine message type
        $isCommand = str_starts_with($text, '/');
        $type = $isCommand ? MessageType::COMMAND : MessageType::TEXT;

        // Extract command and arguments if it's a command
        $command = null;
        $commandArgs = null;
        if ($isCommand) {
            $parts = explode(' ', $text, 2);
            $command = $parts[0];
            $commandArgs = isset($parts[1]) ? explode(' ', $parts[1]) : [];
        }

        return new IncomingMessage(
            platform: 'telegram',
            messageId: (string) $message->getMessageId(),
            type: $type,
            chatId: (string) $chat->getId(),
            userId: (string) $from->getId(),
            username: $from->getUsername(),
            firstName: $from->getFirstName(),
            lastName: $from->getLastName(),
            text: $text,
            command: $command,
            commandArgs: $commandArgs,
            callbackData: null,
            metadata: [
                'chat' => $chat,
                'from' => $from,
                'chat_type' => $chat->getType(),
                'message_id' => $message->getMessageId(),
            ],
        );
    }

    /**
     * Parse Telegram callback query into IncomingMessage
     */
    private function parseCallbackQuery(CallbackQuery $callbackQuery): IncomingMessage
    {
        $from = $callbackQuery->getFrom();
        $message = $callbackQuery->getMessage();
        $chat = $message?->getChat();

        return new IncomingMessage(
            platform: 'telegram',
            messageId: (string) ($message?->getMessageId() ?? $callbackQuery->getId()),
            type: MessageType::CALLBACK,
            chatId: (string) ($chat?->getId() ?? $from->getId()),
            userId: (string) $from->getId(),
            username: $from->getUsername(),
            firstName: $from->getFirstName(),
            lastName: $from->getLastName(),
            text: null,
            command: null,
            commandArgs: null,
            callbackData: $callbackQuery->getData(),
            metadata: [
                'callback_id' => $callbackQuery->getId(),
                'from' => $from,
                'message' => $message,
                'chat' => $chat,
                'chat_type' => $chat?->getType() ?? 'private',
            ],
        );
    }

    /**
     * Build Telegram inline keyboard from button array
     */
    private function buildInlineKeyboard(array $buttons): InlineKeyboardMarkup
    {
        $keyboard = [];

        foreach ($buttons as $row) {
            // Ensure row is an array
            if (!is_array($row)) {
                $row = [$row];
            }

            // Convert Button DTOs to Telegram format
            $telegramRow = [];
            foreach ($row as $button) {
                if ($button instanceof Button) {
                    $telegramRow[] = $this->buttonToTelegram($button);
                } elseif (is_array($button)) {
                    // Already in Telegram format
                    $telegramRow[] = $button;
                } else {
                    // Skip invalid buttons
                    continue;
                }
            }

            if (!empty($telegramRow)) {
                $keyboard[] = $telegramRow;
            }
        }

        return new InlineKeyboardMarkup($keyboard);
    }

    /**
     * Convert Button DTO to Telegram button format
     */
    private function buttonToTelegram(Button $button): array
    {
        $telegramButton = [
            'text' => $button->label,
        ];

        if ($button->action === ButtonAction::Callback) {
            $telegramButton['callback_data'] = $button->value;
        } elseif ($button->action === ButtonAction::Url) {
            $telegramButton['url'] = $button->value;
        }

        return $telegramButton;
    }

    /**
     * Build Telegram reply keyboard from keyboard array
     */
    private function buildReplyKeyboard(array $keyboard): array
    {
        return [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];
    }

    /**
     * Get the underlying BotApi instance (for advanced use cases)
     */
    public function getBotApi(): BotApi
    {
        return $this->bot;
    }

    /**
     * React to a message with an emoji
     *
     * @param string $chatId The chat ID
     * @param int $messageId The message ID to react to
     * @param string $emoji The reaction emoji (e.g., '👍', '👎', '❤️')
     * @param bool $isBig Whether to show a big reaction animation
     * @return void
     */
    public function setMessageReaction(string $chatId, int $messageId, string $emoji, bool $isBig = false): void
    {
        // The Telegram API expects the reaction parameter as a JSON-encoded string
        $reaction = json_encode([
            ['type' => 'emoji', 'emoji' => $emoji]
        ]);

        $this->bot->call('setMessageReaction', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reaction' => $reaction,
            'is_big' => $isBig,
        ]);
    }

    /**
     * Parse platform-specific callback query into normalized IncomingCallback
     */
    public function parseCallback(array $payload): IncomingCallback
    {
        $update = Update::fromResponse($payload);
        $callbackQuery = $update->getCallbackQuery();

        if (!$callbackQuery) {
            throw new \InvalidArgumentException('Payload does not contain a callback query');
        }

        return IncomingCallback::fromTelegram([
            'id' => $callbackQuery->getId(),
            'data' => $callbackQuery->getData(),
            'from' => [
                'id' => $callbackQuery->getFrom()->getId(),
                'username' => $callbackQuery->getFrom()->getUsername(),
                'first_name' => $callbackQuery->getFrom()->getFirstName(),
                'last_name' => $callbackQuery->getFrom()->getLastName(),
            ],
            'message' => [
                'chat' => [
                    'id' => $callbackQuery->getMessage()?->getChat()?->getId(),
                ],
            ],
        ]);
    }
}
