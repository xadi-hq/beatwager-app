<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Group;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\DB;

/**
 * Handle /leaderboard command - Show top 10 players in the group
 */
class LeaderboardCommandHandler extends AbstractCommandHandler
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
                    "❌ Please use /leaderboard in a group chat to see that group's rankings."
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

        // Get top 10 users by points in this group
        $leaderboard = DB::table('group_user')
            ->join('users', 'group_user.user_id', '=', 'users.id')
            ->where('group_user.group_id', $group->id)
            ->select(
                'users.name',
                'group_user.points',
                'group_user.points_earned',
                'group_user.points_spent',
                DB::raw('(SELECT COUNT(*) FROM wager_entries WHERE wager_entries.user_id = users.id AND wager_entries.group_id = group_user.group_id AND wager_entries.result = \'won\') as wins'),
                DB::raw('(SELECT COUNT(*) FROM wager_entries WHERE wager_entries.user_id = users.id AND wager_entries.group_id = group_user.group_id AND wager_entries.result = \'lost\') as losses')
            )
            ->orderBy('group_user.points', 'desc')
            ->limit(10)
            ->get();

        // Generate URL to group leaderboard page
        $fullUrl = route('groups.show', ['group' => $group->id]) . '?tab=leaderboard';

        // Create short URL
        $shortCode = ShortUrl::generateUniqueCode(6);
        ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $fullUrl,
            'expires_at' => now()->addDay(),
        ]);
        $shortUrl = url('/l/' . $shortCode);

        // Format and send message
        $responseMessage = $this->formatLeaderboardMessage($leaderboard, $group, $shortUrl);

        $this->messenger->sendMessage(
            OutgoingMessage::markdown($message->chatId, $responseMessage)
        );
    }

    private function formatLeaderboardMessage($leaderboard, Group $group, string $shortUrl): string
    {
        $currency = $group->points_currency_name ?? 'points';
        $groupName = $group->name ?? $group->platform_chat_title;

        $message = "🏆 *Leaderboard: {$groupName}*\n\n";

        if ($leaderboard->isEmpty()) {
            $message .= "No players yet.\n";
            $message .= "Place your first wager to get started!\n\n";
        } else {
            foreach ($leaderboard as $i => $player) {
                $rank = $i + 1;
                $medal = $this->getMedal($rank);
                $name = $player->name;
                $points = number_format($player->points);
                $wins = $player->wins;
                $losses = $player->losses;
                $winRate = ($wins + $losses > 0) ? round(($wins / ($wins + $losses)) * 100) : 0;

                $message .= "{$medal} *{$name}*\n";
                $message .= "   💰 {$points} {$currency}";

                if ($wins + $losses > 0) {
                    $message .= " • {$wins}W-{$losses}L ({$winRate}%)";
                }

                $message .= "\n\n";
            }
        }

        $message .= "👉 Full stats: {$shortUrl}";

        return $message;
    }

    private function getMedal(int $rank): string
    {
        return match ($rank) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => "{$rank}.",
        };
    }

    public function getCommand(): string
    {
        return '/leaderboard';
    }

    public function getDescription(): string
    {
        return 'View top 10 players and their stats';
    }
}
