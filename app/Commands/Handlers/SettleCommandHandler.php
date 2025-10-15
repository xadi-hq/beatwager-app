<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\DTOs\Button;
use App\DTOs\ButtonAction;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Challenge;
use App\Models\GroupEvent;
use App\Models\Wager;
use Illuminate\Support\Facades\Log;

/**
 * Handle /settle command - Show unsettled items with action buttons
 * Only works in group contexts
 */
class SettleCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingMessage $message): void
    {
        // Only allow in group contexts
        if (!$message->isGroupContext()) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "⚠️ The /settle command only works in group chats. Use the web app to settle items from DMs."
                )
            );
            return;
        }

        // Get the group from the chat
        $group = \App\Models\Group::where('platform_chat_id', $message->chatId)
            ->where('platform', 'telegram')
            ->first();

        if (!$group) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "⚠️ This group is not registered. Use /start to set up BeatWager."
                )
            );
            return;
        }

        // Collect unsettled items
        $unsettledItems = $this->getUnsettledItems($group);

        if (empty($unsettledItems)) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "✅ All caught up! No items need settling right now."
                )
            );
            return;
        }

        // Build message with buttons (max 5 most urgent items)
        // Step 1: Show list of items to settle (one button per item)
        $messageText = "⏰ *Items Ready to Settle*\n\n";
        $messageText .= "Here are the items that need your attention:\n";
        $messageText .= "_Tap an item to settle it_";

        $buttons = [];
        $count = 0;

        foreach ($unsettledItems as $item) {
            if ($count >= 5) {
                break;
            }

            // Create a single button per item (full width)
            // The callback will trigger showing settlement options (Step 2)
            $buttons[] = [
                new Button(
                    label: "{$item['emoji']} {$item['title']}",
                    action: ButtonAction::Callback,
                    value: "settle_item:{$item['type']}:{$item['id']}"
                )
            ];

            $count++;
        }

        $this->messenger->sendMessage(
            OutgoingMessage::withButtons($message->chatId, $messageText, $buttons)
        );
    }

    /**
     * Get all unsettled items for a group, sorted by urgency
     */
    private function getUnsettledItems(\App\Models\Group $group): array
    {
        $items = [];

        // 1. Unsettled Wagers (past deadline with entries)
        $unsettledWagers = Wager::where('group_id', $group->id)
            ->where('status', 'open')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('expected_settlement_at')
                        ->where('expected_settlement_at', '<', now());
                })->orWhere(function ($q) {
                    $q->whereNull('expected_settlement_at')
                        ->where('betting_closes_at', '<', now());
                });
            })
            ->whereHas('entries') // Only wagers with entries
            ->orderBy('expected_settlement_at')
            ->orderBy('betting_closes_at')
            ->limit(5)
            ->get();

        foreach ($unsettledWagers as $wager) {
            $items[] = [
                'type' => 'wager',
                'id' => $wager->id,
                'title' => $wager->title,
                'emoji' => '🎲',
                'urgency' => $wager->expected_settlement_at ?? $wager->betting_closes_at,
                'model' => $wager,
            ];
        }

        // 2. Events past date with "going" RSVPs but no attendance recorded
        $unsettledEvents = GroupEvent::where('group_id', $group->id)
            ->where('status', 'upcoming')
            ->where('event_date', '<', now())
            ->whereHas('rsvps', function ($query) {
                $query->where('response', 'going');
            })
            ->whereDoesntHave('attendance')
            ->orderBy('event_date')
            ->limit(3)
            ->get();

        foreach ($unsettledEvents as $event) {
            $items[] = [
                'type' => 'event',
                'id' => $event->id,
                'title' => $event->name,
                'emoji' => '📅',
                'urgency' => $event->event_date,
                'model' => $event,
            ];
        }

        // 3. Challenges past deadline that are accepted but not verified
        $unsettledChallenges = Challenge::where('group_id', $group->id)
            ->where('status', 'accepted')
            ->where('completion_deadline', '<', now())
            ->orderBy('completion_deadline')
            ->limit(3)
            ->get();

        foreach ($unsettledChallenges as $challenge) {
            $items[] = [
                'type' => 'challenge',
                'id' => $challenge->id,
                'title' => $challenge->description,
                'emoji' => '💪',
                'urgency' => $challenge->completion_deadline,
                'model' => $challenge,
            ];
        }

        // Sort by urgency (oldest first)
        usort($items, function ($a, $b) {
            return $a['urgency'] <=> $b['urgency'];
        });

        return $items;
    }

    // Note: Settlement options (Step 2) are now handled by SettleItemCallbackHandler
    // This keeps the /settle command UI clean with one button per item

    public function getCommand(): string
    {
        return '/settle';
    }

    public function getDescription(): string
    {
        return 'Show unsettled wagers, events, and challenges with quick actions';
    }
}
