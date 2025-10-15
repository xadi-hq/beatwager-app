<?php

declare(strict_types=1);

namespace App\Services\Messengers;

use App\Contracts\MessengerInterface;
use App\DTOs\Button;
use App\DTOs\ButtonAction;
use App\DTOs\Message;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Telegram messenger adapter
 *
 * Formats platform-agnostic messages for Telegram and sends them.
 *
 * @deprecated Use App\Messaging\Adapters\TelegramAdapter instead
 * This class is being replaced by TelegramAdapter which provides DM support
 * and a more comprehensive feature set. MessengerBridge now wraps TelegramAdapter
 * for backward compatibility.
 */
class TelegramMessenger implements MessengerInterface
{
    private BotApi $bot;

    public function __construct()
    {
        $this->bot = new BotApi(config('telegram.bot_token'));
    }

    /**
     * Send message to Telegram chat
     */
    public function send(Message $message, string $chatId): void
    {
        $formattedMessage = $this->formatMessage($message);
        $keyboard = $this->buildButtons($message->buttons);

        try {
            $this->bot->sendMessage(
                $chatId,
                $formattedMessage,
                'HTML',
                false,
                null,
                $keyboard
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send Telegram message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Format message content as Telegram HTML
     */
    public function formatMessage(Message $message): string
    {
        // Get content with variables replaced
        $content = $message->getFormattedContent();

        // Convert Markdown-style formatting to HTML with escaping
        // Bold: **text** -> <b>escaped(text)</b>
        $content = preg_replace_callback('/\*\*(.+?)\*\*/s', function($matches) {
            return '<b>' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</b>';
        }, $content);

        // Italic: *text* or _text_ -> <i>escaped(text)</i>
        $content = preg_replace_callback('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', function($matches) {
            return '<i>' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</i>';
        }, $content);
        $content = preg_replace_callback('/_(.+?)_/s', function($matches) {
            return '<i>' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</i>';
        }, $content);

        // Code: `text` -> <code>escaped(text)</code>
        $content = preg_replace_callback('/`(.+?)`/s', function($matches) {
            return '<code>' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</code>';
        }, $content);

        // Escape any remaining unformatted text
        // Split by HTML tags we just created, escape the parts between them
        $content = preg_replace_callback('/(<[^>]+>)|([^<]+)/', function($matches) {
            // If it's an HTML tag, keep it as is
            if (!empty($matches[1])) {
                return $matches[1];
            }
            // Otherwise escape it
            return htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
        }, $content);

        // Apply Telegram-specific formatting
        // Bold key-value pairs like "Question: ..."
        $content = preg_replace('/^(\w+:)/m', '<b>$1</b>', $content);

        return $content;
    }

    /**
     * Build Telegram inline keyboard from Button DTOs
     */
    public function buildButtons(array $buttons): ?InlineKeyboardMarkup
    {
        if (empty($buttons)) {
            return null;
        }

        // Normalize buttons to handle both flat arrays and pre-organized rows
        $rows = [];

        foreach ($buttons as $item) {
            // If item is an array, it's already a row of buttons
            if (is_array($item) && !empty($item) && $item[0] instanceof Button) {
                $row = [];
                foreach ($item as $button) {
                    $row[] = match ($button->action) {
                        ButtonAction::Callback => ['text' => $button->label, 'callback_data' => $button->value],
                        ButtonAction::Url => ['text' => $button->label, 'url' => $button->value],
                    };
                }
                $rows[] = $row;
            }
            // Otherwise it's a single Button object (legacy flat array)
            elseif ($item instanceof Button) {
                $rows[] = [match ($item->action) {
                    ButtonAction::Callback => ['text' => $item->label, 'callback_data' => $item->value],
                    ButtonAction::Url => ['text' => $item->label, 'url' => $item->value],
                }];
            }
        }

        return new InlineKeyboardMarkup($rows);
    }
}