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
        
        // Apply Telegram-specific formatting
        // Bold for emphasized text (first line usually)
        $lines = explode("\n", $content);
        if (isset($lines[0])) {
            $lines[0] = "<b>{$lines[0]}</b>";
        }
        
        // Bold key-value pairs like "Question: ..." 
        $content = implode("\n", $lines);
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