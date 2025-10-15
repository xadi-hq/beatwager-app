<?php

declare(strict_types=1);

namespace App\Services\Messengers;

use App\Contracts\MessengerInterface;
use App\DTOs\Button;
use App\DTOs\ButtonAction;
use App\DTOs\Message;
use App\Messaging\MessengerAdapterInterface;
use App\Messaging\DTOs\OutgoingMessage;

/**
 * Bridge adapter between old MessengerInterface and new MessengerAdapterInterface
 *
 * This allows gradual migration from the old system to the new one without breaking existing code.
 * The old system used App\DTOs\Message, the new system uses App\Messaging\DTOs\OutgoingMessage.
 *
 * @deprecated This bridge will be removed once all code migrates to MessengerAdapterInterface
 */
class MessengerBridge implements MessengerInterface
{
    public function __construct(
        private readonly MessengerAdapterInterface $adapter
    ) {}

    /**
     * Send message using the old interface, converted to new system
     */
    public function send(Message $message, string $chatId): void
    {
        $outgoingMessage = $this->convertToOutgoingMessage($message, $chatId);
        $this->adapter->sendMessage($outgoingMessage);
    }

    /**
     * Format message for display (old interface)
     * Note: New system handles formatting internally, so this just returns formatted content
     */
    public function formatMessage(Message $message): string
    {
        return $message->getFormattedContent();
    }

    /**
     * Build buttons (old interface)
     * Note: New system handles button building internally, so this returns the raw array
     */
    public function buildButtons(array $buttons): mixed
    {
        // Convert Button DTOs to simple array format for the new system
        return array_map(function (Button $button) {
            return [
                'text' => $button->label,
                'action' => $button->action->value,
                'value' => $button->value,
            ];
        }, $buttons);
    }

    /**
     * Convert old Message DTO to new OutgoingMessage DTO
     */
    private function convertToOutgoingMessage(Message $message, string $chatId): OutgoingMessage
    {
        // Get formatted content with variables replaced
        $text = $message->getFormattedContent();

        // Convert buttons if present
        $buttons = null;
        if (!empty($message->buttons)) {
            // Convert button rows from Button objects to arrays while preserving row structure
            // Input: [[Button, Button], [Button]] -> Output: [[button_array, button_array], [button_array]]
            $buttons = [];
            foreach ($message->buttons as $row) {
                // Handle both array of buttons (rows) and single buttons
                $buttonRow = is_array($row) ? $row : [$row];

                $convertedRow = [];
                foreach ($buttonRow as $button) {
                    if ($button instanceof Button) {
                        // Build button array - only include the relevant action field
                        $buttonData = ['text' => $button->label];

                        if ($button->action === ButtonAction::Callback) {
                            $buttonData['callback_data'] = $button->value;
                        } elseif ($button->action === ButtonAction::Url) {
                            $buttonData['url'] = $button->value;
                        }

                        $convertedRow[] = $buttonData;
                    }
                }

                if (!empty($convertedRow)) {
                    $buttons[] = $convertedRow;
                }
            }
        }

        // Create OutgoingMessage with appropriate parse mode
        // Old system uses HTML, so we'll use HTML in the new system too
        return new OutgoingMessage(
            chatId: $chatId,
            text: $text,
            parseMode: 'HTML',  // Old TelegramMessenger used HTML
            buttons: $buttons,
            keyboard: null,
            disablePreview: false,
            metadata: []
        );
    }


    /**
     * Direct access to underlying adapter for new features
     * This allows code to use DM sending and other new features while still using the bridge
     */
    public function getAdapter(): MessengerAdapterInterface
    {
        return $this->adapter;
    }
}
