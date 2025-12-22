<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Enums\ChallengeType;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Challenge;
use App\Models\Group;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Log;

/**
 * Handle /challenges command - Show top 3 most recent open challenges in the group
 * Includes 1-on-1 challenges, super challenges, and elimination challenges
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
                    "Challenges are contests between group members!"
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

        // Get top 3 most recent active challenges (all types)
        // - 1-on-1: status pending or accepted
        // - Super/Elimination: status open
        $challenges = Challenge::where('group_id', $group->id)
            ->where(function ($q) {
                // 1-on-1 challenges with pending/accepted status
                $q->where(function ($q2) {
                    $q2->where('type', ChallengeType::USER_CHALLENGE->value)
                       ->whereIn('status', ['pending', 'accepted']);
                })
                // Super challenges that are open
                ->orWhere(function ($q2) {
                    $q2->where('type', ChallengeType::SUPER_CHALLENGE->value)
                       ->where('status', 'open')
                       ->where('completion_deadline', '>=', now());
                })
                // Elimination challenges that are open
                ->orWhere(function ($q2) {
                    $q2->where('type', ChallengeType::ELIMINATION_CHALLENGE->value)
                       ->where('status', 'open');
                });
            })
            ->with(['creator', 'acceptor', 'participants'])
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

                $message .= ($i + 1) . ". *{$description}*\n";

                if ($challenge->isEliminationChallenge()) {
                    $message .= $this->formatEliminationChallenge($challenge, $currency);
                } elseif ($challenge->isSuperChallenge()) {
                    $message .= $this->formatSuperChallenge($challenge, $currency);
                } else {
                    $message .= $this->formatUserChallenge($challenge, $currency);
                }
            }

            // Show total count if more than 3
            $totalOpen = $this->getTotalOpenCount($group);

            if ($totalOpen > 3) {
                $message .= "_{$totalOpen} total open challenges_\n\n";
            }
        }

        $message .= "👉 View all: {$shortUrl}";

        return $message;
    }

    private function formatUserChallenge(Challenge $challenge, string $currency): string
    {
        $creatorName = $challenge->creator->name;
        $status = $challenge->status;
        $amount = $challenge->amount;

        $result = "   👤 {$creatorName}";

        if ($status === 'pending') {
            $deadline = $this->formatDeadline($challenge->acceptance_deadline);
            $result .= " • 🔓 Open ({$deadline})";
        } else {
            $acceptorName = $challenge->acceptor?->name ?? 'Unknown';
            $deadline = $this->formatDeadline($challenge->completion_deadline);
            $result .= " vs {$acceptorName} • ⏰ {$deadline}";
        }

        $result .= "\n   💰 {$amount} {$currency}\n\n";

        return $result;
    }

    private function formatSuperChallenge(Challenge $challenge, string $currency): string
    {
        $participantCount = $challenge->participants->count();
        $maxParticipants = $challenge->max_participants;
        $prizePerPerson = $challenge->prize_per_person;
        $deadline = $this->formatDeadline($challenge->completion_deadline);

        $result = "   🏆 Super Challenge • {$participantCount}";
        if ($maxParticipants) {
            $result .= "/{$maxParticipants}";
        }
        $result .= " joined\n";
        $result .= "   💰 {$prizePerPerson} {$currency}/person • ⏰ {$deadline}\n\n";

        return $result;
    }

    private function formatEliminationChallenge(Challenge $challenge, string $currency): string
    {
        $survivorCount = $challenge->getSurvivorCount();
        $participantCount = $challenge->participants->count();
        $pot = $challenge->point_pot;
        $deadline = $this->formatDeadline($challenge->completion_deadline);

        $result = "   💀 Elimination • {$survivorCount}/{$participantCount} surviving\n";
        $result .= "   💰 {$pot} {$currency} pot • ⏰ {$deadline}\n\n";

        return $result;
    }

    private function getTotalOpenCount(Group $group): int
    {
        return Challenge::where('group_id', $group->id)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('type', ChallengeType::USER_CHALLENGE->value)
                       ->whereIn('status', ['pending', 'accepted']);
                })
                ->orWhere(function ($q2) {
                    $q2->where('type', ChallengeType::SUPER_CHALLENGE->value)
                       ->where('status', 'open')
                       ->where('completion_deadline', '>=', now());
                })
                ->orWhere(function ($q2) {
                    $q2->where('type', ChallengeType::ELIMINATION_CHALLENGE->value)
                       ->where('status', 'open');
                });
            })
            ->count();
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
