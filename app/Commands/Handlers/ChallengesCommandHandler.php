<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Challenge;
use App\Models\Group;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Log;

/**
 * Handle /challenges command - Show top 3 most recent open challenges in the group
 */
class ChallengesCommandHandler extends AbstractCommandHandler
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
                    "❌ Please use /challenges in a group chat to see that group's challenges.\n\n" .
                    "Challenges are 1-on-1 contests between group members!"
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
                    "Create your first challenge with /newchallenge to get started!"
                )
            );
            return;
        }

        // Get top 3 most recent open challenges (pending or accepted but not completed)
        $challenges = Challenge::where('group_id', $group->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->with(['creator', 'acceptor'])
            ->orderBy('created_at', 'desc')
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
        $responseMessage = $this->formatChallengesMessage($challenges, $group, $shortUrl);

        $this->messenger->sendMessage(
            OutgoingMessage::markdown($message->chatId, $responseMessage)
        );
    }

    private function formatChallengesMessage($challenges, Group $group, string $shortUrl): string
    {
        $count = $challenges->count();
        $currency = $group->points_currency_name ?? 'points';
        $groupName = $group->name ?? $group->platform_chat_title;

        $message = "💪 *Open Challenges in {$groupName}*\n\n";

        if ($count === 0) {
            $message .= "No open challenges yet.\n";
            $message .= "Be the first! Use /newchallenge to create one.\n\n";
        } else {
            foreach ($challenges as $i => $challenge) {
                $description = $this->truncateDescription($challenge->description);
                $creatorName = $challenge->creator->name;
                $status = $challenge->status;
                $amount = $challenge->amount;

                $message .= ($i + 1) . ". *{$description}*\n";
                $message .= "   👤 {$creatorName}";

                if ($status === 'pending') {
                    $deadline = $this->formatDeadline($challenge->acceptance_deadline);
                    $message .= " • 🔓 Open ({$deadline})";
                } else {
                    $acceptorName = $challenge->acceptor?->name ?? 'Unknown';
                    $deadline = $this->formatDeadline($challenge->completion_deadline);
                    $message .= " vs {$acceptorName} • ⏰ {$deadline}";
                }

                $message .= "\n   💰 {$amount} {$currency}\n\n";
            }

            // Show total count if more than 3
            $totalOpen = Challenge::where('group_id', $group->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->count();

            if ($totalOpen > 3) {
                $message .= "_{$totalOpen} total open challenges_\n\n";
            }
        }

        $message .= "👉 View all: {$shortUrl}";

        return $message;
    }

    private function formatDeadline(?\DateTimeInterface $deadline): string
    {
        if (!$deadline) {
            return 'No deadline';
        }

        $now = now();

        if ($deadline < $now) {
            return 'Expired';
        }

        $diff = $now->diff($deadline);

        if ($diff->days > 0) {
            return $diff->days . 'd left';
        } elseif ($diff->h > 0) {
            return $diff->h . 'h left';
        } else {
            return $diff->i . 'm left';
        }
    }

    private function truncateDescription(string $description): string
    {
        return strlen($description) > 60 ? substr($description, 0, 57) . '...' : $description;
    }

    public function getCommand(): string
    {
        return '/challenges';
    }

    public function getDescription(): string
    {
        return 'Show top 3 most recent open challenges in this group';
    }
}
