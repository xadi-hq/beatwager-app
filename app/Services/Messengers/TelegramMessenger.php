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

        // Group buttons by rows based on wager buttons vs view progress
        // First buttons are wager options (chunk by 3 for multiple choice, or 2 for binary)
        // Last button is usually View Progress (full width)
        
        $rows = [];
        $currentRow = [];
        $lastButtonIndex = count($buttons) - 1;
        
        foreach ($buttons as $index => $button) {
            $telegramButton = match ($button->action) {
                ButtonAction::Callback => ['text' => $button->label, 'callback_data' => $button->value],
                ButtonAction::Url => ['text' => $button->label, 'url' => $button->value],
            };

            // If this is the last button and it's a View Progress or URL button, put it on its own row
            if ($index === $lastButtonIndex &&
                ($button->action === ButtonAction::Url || str_contains($button->value, 'view:'))) {
                if (!empty($currentRow)) {
                    $rows[] = $currentRow;
                    $currentRow = []; // Clear to prevent duplicate addition
                }
                $rows[] = [$telegramButton];
            } else {
                $currentRow[] = $telegramButton;
                
                // For wager buttons, chunk by 3 for multiple choice
                if (count($currentRow) === 3) {
                    $rows[] = $currentRow;
                    $currentRow = [];
                }
            }
        }

        // Add any remaining buttons
        if (!empty($currentRow)) {
            $rows[] = $currentRow;
        }

        return new InlineKeyboardMarkup($rows);
    }
}