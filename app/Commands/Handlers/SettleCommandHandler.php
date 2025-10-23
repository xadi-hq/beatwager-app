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
        $messageText = "⏰ *Items Ready to Settle*\n\n";
        $messageText .= "Here are the items that need your attention:\n";

        $buttons = [];
        $count = 0;

        foreach ($unsettledItems as $item) {
            if ($count >= 5) {
                break;
            }

            $messageText .= "\n• {$item['emoji']} {$item['title']}";
            $buttons[] = $this->buildItemButton($item);
            $count++;
        }

        $this->messenger->sendMessage(
            OutgoingMessage::markdown($message->chatId, $messageText)
                ->withButtons($buttons)
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

    /**
     * Build appropriate button for each item type
     */
    private function buildItemButton(array $item): array
    {
        return match ($item['type']) {
            'wager' => $this->buildWagerButtons($item['model']),
            'event' => $this->buildEventButton($item['model']),
            'challenge' => $this->buildChallengeButtons($item['model']),
            default => [],
        };
    }

    /**
     * Build settlement buttons for a wager (based on type)
     */
    private function buildWagerButtons(Wager $wager): array
    {
        return match ($wager->type) {
            'binary' => [
                new Button(
                    label: "Yes - {$wager->title}",
                    action: ButtonAction::Callback,
                    value: "settle_wager:{$wager->id}:yes"
                ),
                new Button(
                    label: "No - {$wager->title}",
                    action: ButtonAction::Callback,
                    value: "settle_wager:{$wager->id}:no"
                ),
            ],
            'multiple_choice' => collect($wager->options)
                ->take(3) // Limit to 3 for button space
                ->map(fn ($opt) => new Button(
                    label: "{$opt} - " . \Illuminate\Support\Str::limit($wager->title, 20),
                    action: ButtonAction::Callback,
                    value: "settle_wager:{$wager->id}:" . strtolower($opt)
                ))
                ->toArray(),
            default => [
                new Button(
                    label: "Settle: {$wager->title}",
                    action: ButtonAction::Callback,
                    value: "view:{$wager->id}"
                ),
            ],
        };
    }

    /**
     * Build event button (link to web app for attendance)
     */
    private function buildEventButton(GroupEvent $event): array
    {
        // Generate signed URL
        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'event.show',
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

        return [
            new Button(
                label: "Record Attendance - {$event->name}",
                action: ButtonAction::Url,
                value: $shortUrl
            ),
        ];
    }

    /**
     * Build challenge verification buttons
     */
    private function buildChallengeButtons(Challenge $challenge): array
    {
        return [
            new Button(
                label: "✅ Yes - " . \Illuminate\Support\Str::limit($challenge->description, 25),
                action: ButtonAction::Callback,
                value: "settle_challenge:{$challenge->id}:yes"
            ),
            new Button(
                label: "❌ No - " . \Illuminate\Support\Str::limit($challenge->description, 25),
                action: ButtonAction::Callback,
                value: "settle_challenge:{$challenge->id}:no"
            ),
        ];
    }

    public function getCommand(): string
    {
        return '/settle';
    }

    public function getDescription(): string
    {
        return 'Show unsettled wagers, events, and challenges with quick actions';
    }
}
