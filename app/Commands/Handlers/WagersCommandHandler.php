<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Group;
use App\Models\ShortUrl;
use App\Models\Wager;
use Illuminate\Support\Facades\Log;

/**
 * Handle /wagers command - Show top 3 most recent open wagers in the group
 */
class WagersCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        protected readonly MessengerAdapterInterface $messenger
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
                    "❌ Please use /wagers in a group chat to see that group's wagers.\n\n" .
                    "Use /mywagers to see your personal wagers across all groups!"
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
                    "Create your first wager with /newwager to get started!"
                )
            );
            return;
        }

        // Get top 3 most recent open wagers for this group, ordered by deadline
        $wagers = Wager::where('group_id', $group->id)
            ->where('status', 'open')
            ->orderBy('betting_closes_at', 'asc')
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
        $responseMessage = $this->formatWagersMessage($wagers, $group, $shortUrl);

        $this->messenger->sendMessage(
            OutgoingMessage::markdown($message->chatId, $responseMessage)
        );
    }

    private function formatWagersMessage($wagers, Group $group, string $shortUrl): string
    {
        $count = $wagers->count();
        $currency = $group->points_currency_name ?? 'points';
        $groupName = $group->name ?? $group->platform_chat_title;

        $message = "🎲 *Open Wagers in {$groupName}*\n\n";

        if ($count === 0) {
            $message .= "No open wagers yet.\n";
            $message .= "Be the first! Use /newwager to create one.\n\n";
        } else {
            foreach ($wagers as $i => $wager) {
                $title = $this->truncateTitle($wager->title);
                $timeLeft = $this->formatTimeLeft($wager->betting_closes_at);
                $participants = $wager->participants_count;
                $pot = $wager->total_points_wagered;

                $message .= ($i + 1) . ". *\"{$title}\"*\n";
                $message .= "   ⏰ Closes: {$timeLeft}\n";
                $message .= "   👥 {$participants} participant" . ($participants != 1 ? 's' : '');
                $message .= " • 💰 {$pot} {$currency}\n\n";
            }

            // Show total count if more than 3
            $totalOpen = Wager::where('group_id', $group->id)
                ->where('status', 'open')
                ->count();

            if ($totalOpen > 3) {
                $message .= "_{$totalOpen} total open wagers_\n\n";
            }
        }

        $message .= "👉 View all: {$shortUrl}";

        return $message;
    }

    private function formatTimeLeft(\DateTimeInterface $deadline): string
    {
        $now = now();

        if ($deadline < $now) {
            return 'Awaiting settlement';
        }

        $diff = $now->diff($deadline);

        if ($diff->days > 0) {
            return $diff->days . 'd ' . $diff->h . 'h';
        } elseif ($diff->h > 0) {
            return $diff->h . 'h ' . $diff->i . 'm';
        } else {
            return $diff->i . 'm';
        }
    }

    private function truncateTitle(string $title): string
    {
        return strlen($title) > 50 ? substr($title, 0, 47) . '...' : $title;
    }

    public function getCommand(): string
    {
        return '/wagers';
    }

    public function getDescription(): string
    {
        return 'Show top 3 most recent open wagers in this group';
    }
}
