<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\DTOs\Button;
use App\DTOs\ButtonAction;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Challenge;
use App\Models\GroupEvent;
use App\Models\Wager;
use Illuminate\Support\Facades\Log;

/**
 * Handle settle_item callback - Step 2: Show settlement options for selected item
 */
class SettleItemCallbackHandler extends AbstractCallbackHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "settle_item:{type}:{id}"
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid format',
                showAlert: true
            );
            return;
        }

        // Parse callback data
        $parts = explode(':', $callback->data);
        if (count($parts) !== 2) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid format',
                showAlert: true
            );
            return;
        }

        [$type, $id] = $parts;

        try {
            // Load the item based on type
            $item = match ($type) {
                'wager' => Wager::with(['group'])->find($id),
                'event' => GroupEvent::with(['group'])->find($id),
                'challenge' => Challenge::with(['group'])->find($id),
                default => null,
            };

            if (!$item) {
                $this->messenger->answerCallback(
                    $callback->callbackId,
                    '❌ Item not found',
                    showAlert: true
                );
                return;
            }

            // Build settlement options based on type
            [$messageText, $buttons] = $this->buildSettlementOptions($type, $item);

            // Answer the callback
            $this->messenger->answerCallback($callback->callbackId, '');

            // Send new message with settlement options
            $this->messenger->sendMessage(
                OutgoingMessage::withButtons($callback->chatId, $messageText, $buttons)
            );

        } catch (\Exception $e) {
            Log::error('Error showing settlement options', [
                'error' => $e->getMessage(),
                'type' => $type,
                'id' => $id,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error loading item',
                showAlert: true
            );
        }
    }

    /**
     * Build settlement options based on item type
     */
    private function buildSettlementOptions(string $type, mixed $item): array
    {
        return match ($type) {
            'wager' => $this->buildWagerOptions($item),
            'event' => $this->buildEventOptions($item),
            'challenge' => $this->buildChallengeOptions($item),
            default => ['Invalid item type', []],
        };
    }

    /**
     * Build settlement options for a wager
     */
    private function buildWagerOptions(Wager $wager): array
    {
        $messageText = "🎲 *{$wager->title}*\n\n";
        $messageText .= "Please select the final result:";

        $buttons = match ($wager->type) {
            'binary' => [
                [
                    new Button(
                        label: '✅ Yes',
                        action: ButtonAction::Callback,
                        value: "settle_wager:{$wager->id}:yes"
                    ),
                    new Button(
                        label: '❌ No',
                        action: ButtonAction::Callback,
                        value: "settle_wager:{$wager->id}:no"
                    ),
                ],
            ],
            'multiple_choice' => array_chunk(
                collect($wager->options)
                    ->map(fn($opt) => new Button(
                        label: $opt,
                        action: ButtonAction::Callback,
                        value: "settle_wager:{$wager->id}:" . strtolower($opt)
                    ))
                    ->toArray(),
                3 // Max 3 per row
            ),
            default => [],
        };

        return [$messageText, $buttons];
    }

    /**
     * Build settlement options for an event
     */
    private function buildEventOptions(GroupEvent $event): array
    {
        $messageText = "📅 *{$event->name}*\n\n";
        $messageText .= "Record attendance on the web app:";

        // Generate signed URL directly to attendance recording page
        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'events.attendance',
            now()->addDays(7),
            ['event' => $event->id]
        );

        // Create short URL
        $shortCode = \App\Models\ShortUrl::generateUniqueCode(6);
        \App\Models\ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $signedUrl,
            'expires_at' => now()->addDays(7),
        ]);

        $shortUrl = url('/l/' . $shortCode);

        $buttons = [
            [
                new Button(
                    label: '📊 Record Attendance',
                    action: ButtonAction::Url,
                    value: $shortUrl
                ),
            ],
        ];

        return [$messageText, $buttons];
    }

    /**
     * Build settlement options for a challenge
     */
    private function buildChallengeOptions(Challenge $challenge): array
    {
        $messageText = "💪 *Challenge*\n\n";
        $messageText .= "_{$challenge->description}_\n\n";
        $messageText .= "Was the challenge completed successfully?";

        $buttons = [
            [
                new Button(
                    label: '✅ Yes - Completed',
                    action: ButtonAction::Callback,
                    value: "settle_challenge:{$challenge->id}:yes"
                ),
                new Button(
                    label: '❌ No - Failed',
                    action: ButtonAction::Callback,
                    value: "settle_challenge:{$challenge->id}:no"
                ),
            ],
        ];

        return [$messageText, $buttons];
    }

    public function getAction(): string
    {
        return 'settle_item';
    }

    public function getDescription(): string
    {
        return 'Show settlement options for a selected item (Step 2 of settle flow)';
    }
}
