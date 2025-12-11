<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Log;

/**
 * Handle /events command - Show top 3 most recent/upcoming events in the group
 */
class EventsCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingMessage $message): void
    {
        // Only allow in group chats
        if (!$message->isGroupContext()) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "❌ Please use /events in a group chat to see that group's events.\n\n" .
                    "Events are group meetups with attendance tracking!"
                )
            );
            return;
        }

        // Get chat info
        $chat = $message->getChat();

        // Find the group by platform chat ID
        $group = Group::where('platform', $message->platform)
            ->where('platform_chat_id', $chat->getId())
            ->first();

        if (!$group) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "❌ This group is not registered yet.\n\n" .
                    "Create your first event with /newevent to get started!"
                )
            );
            return;
        }

        // Get top 3 upcoming events (future or recent past), ordered by event date
        $now = now();
        $events = GroupEvent::where('group_id', $group->id)
            ->where('event_date', '>=', $now->copy()->subDays(7)) // Include events from last 7 days
            ->orderBy('event_date', 'asc')
            ->limit(3)
            ->get();

        // Generate URL to group page
        $fullUrl = route('groups.show', ['group' => $group->id]);

        // Create short URL
        $shortCode = ShortUrl::generateUniqueCode(6);
        ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $fullUrl,
            'expires_at' => now()->addDay(),
        ]);
        $shortUrl = url('/l/' . $shortCode);

        // Format and send message
        $responseMessage = $this->formatEventsMessage($events, $group, $shortUrl);

        $this->messenger->sendMessage(
            OutgoingMessage::markdown($message->chatId, $responseMessage)
        );
    }

    private function formatEventsMessage($events, Group $group, string $shortUrl): string
    {
        $count = $events->count();
        $currency = $group->points_currency_name ?? 'points';
        $groupName = $group->name ?? $group->platform_chat_title;

        $message = "📅 *Events in {$groupName}*\n\n";

        if ($count === 0) {
            $message .= "No upcoming events.\n";
            $message .= "Be the first! Use /newevent to create one.\n\n";
        } else {
            $now = now();

            foreach ($events as $i => $event) {
                $name = $this->truncateName($event->name);
                // Convert UTC to group timezone for display
                $eventDate = $group->toGroupTimezone($event->event_date);
                $isPast = $event->event_date < $now;
                $timeInfo = $this->formatEventTime($eventDate, $isPast);
                $location = $event->location ? substr($event->location, 0, 30) : 'TBD';
                $bonus = $event->attendance_bonus;

                // Get RSVP counts
                $goingCount = $event->rsvps()->where('response', 'going')->count();
                $maybeCount = $event->rsvps()->where('response', 'maybe')->count();

                $emoji = $isPast ? '✅' : '📍';
                $message .= ($i + 1) . ". {$emoji} *{$name}*\n";
                $message .= "   ⏰ {$timeInfo}\n";
                $message .= "   📍 {$location}\n";
                $message .= "   👥 {$goingCount} going";

                if ($maybeCount > 0) {
                    $message .= ", {$maybeCount} maybe";
                }

                if ($bonus > 0) {
                    $message .= " • 🎁 +{$bonus} {$currency}";
                }

                $message .= "\n\n";
            }

            // Show total upcoming count if more than 3
            $totalUpcoming = GroupEvent::where('group_id', $group->id)
                ->where('event_date', '>=', $now)
                ->count();

            if ($totalUpcoming > 3) {
                $message .= "_{$totalUpcoming} total upcoming events_\n\n";
            }
        }

        $message .= "👉 View all: {$shortUrl}";

        return $message;
    }

    private function formatEventTime(\DateTimeInterface $eventDate, bool $isPast): string
    {
        $now = now();

        if ($isPast) {
            $diff = $now->diff($eventDate);
            if ($diff->days === 0) {
                return 'Today';
            } elseif ($diff->days === 1) {
                return 'Yesterday';
            } else {
                return $diff->days . ' days ago';
            }
        }

        // Future event
        $diff = $now->diff($eventDate);

        if ($diff->days === 0) {
            if ($diff->h > 0) {
                return "Today at " . $eventDate->format('g:i A');
            } else {
                return "In " . $diff->i . " minutes";
            }
        } elseif ($diff->days === 1) {
            return "Tomorrow at " . $eventDate->format('g:i A');
        } elseif ($diff->days < 7) {
            return $eventDate->format('l') . " at " . $eventDate->format('g:i A');
        } else {
            return $eventDate->format('M j') . " at " . $eventDate->format('g:i A');
        }
    }

    private function truncateName(string $name): string
    {
        return strlen($name) > 50 ? substr($name, 0, 47) . '...' : $name;
    }

    public function getCommand(): string
    {
        return '/events';
    }

    public function getDescription(): string
    {
        return 'Show top 3 most recent/upcoming events in this group';
    }
}
