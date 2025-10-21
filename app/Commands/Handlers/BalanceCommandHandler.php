<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Group;
use App\Models\ShortUrl;
use App\Models\Transaction;
use App\Services\UserMessengerService;
use Illuminate\Support\Facades\Log;

/**
 * Handle /balance and /mybalance commands - View points balance
 */
class BalanceCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingMessage $message): void
    {
        // Find or create user with messenger service
        $user = UserMessengerService::findOrCreate(
            platform: $message->platform,
            platformUserId: $message->userId,
            userData: [
                'username' => $message->username,
                'first_name' => $message->firstName,
                'last_name' => $message->lastName,
            ]
        );

        // Determine context: if in group, show that group's balance; if DM, show all groups
        $contextGroup = null;
        $balanceText = '';

        if ($message->isGroupContext()) {
            // Find group by platform chat ID
            $contextGroup = Group::where('platform', $message->platform)
                ->where('platform_chat_id', $message->chatId)
                ->first();

            if (!$contextGroup) {
                $this->messenger->sendMessage(
                    OutgoingMessage::text($message->chatId, '❌ This group is not registered in BeatWager.')
                );
                return;
            }

            // Get user's balance in this group
            $groupUser = $contextGroup->users()->where('users.id', $user->id)->first();

            if (!$groupUser) {
                $this->messenger->sendMessage(
                    OutgoingMessage::text($message->chatId, '❌ You are not a member of this group.')
                );
                return;
            }

            $balance = $groupUser->pivot->points;
            $groupName = $this->escapeMarkdown($contextGroup->name);
            $balanceText = "💰 *Your Balance in {$groupName}*\n\n";
            $balanceText .= "Current: *{$balance} points*\n\n";
        } else {
            // DM context - show summary across all groups
            $groups = $user->groups()->get();

            if ($groups->isEmpty()) {
                $this->messenger->sendMessage(
                    OutgoingMessage::text($message->chatId, '❌ You are not a member of any groups yet.')
                );
                return;
            }

            $balanceText = "💰 *Your Balances Across Groups*\n\n";
            foreach ($groups as $group) {
                $balance = $group->pivot->points;
                $groupName = $this->escapeMarkdown($group->name);
                $balanceText .= "• {$groupName}: *{$balance} pts*\n";
            }
            $balanceText .= "\n";
        }

        // Get recent transactions
        $query = Transaction::where('user_id', $user->id);

        if ($contextGroup) {
            $query->where('group_id', $contextGroup->id);
        }

        $recentTransactions = $query->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        if ($recentTransactions->isNotEmpty()) {
            $balanceText .= "📊 *Recent Activity:*\n";
            foreach ($recentTransactions as $tx) {
                $sign = $tx->amount >= 0 ? '+' : '';
                $emoji = $tx->amount >= 0 ? '📈' : '📉';
                $rawDesc = $tx->description ?: $tx->type;
                // Replace underscores with spaces and title case
                $formatted = str_replace('_', ' ', $rawDesc);
                $desc = $this->escapeMarkdown(ucwords($formatted));
                $balanceText .= "{$emoji} {$sign}{$tx->amount} pts — {$desc}\n";
            }
            $balanceText .= "\n";
        }

        // Generate signed URL for dashboard with transactions focus
        $params = [
            'u' => encrypt($message->platform . ':' . $message->userId),
            'focus' => 'transactions',
        ];
        if ($contextGroup) {
            $params['group_id'] = $contextGroup->id;
        }

        $fullUrl = $this->messenger->createAuthenticatedUrl(
            'dashboard.me',
            $params,
            1440 // 1 day expiry
        );

        // Create short URL
        $shortCode = ShortUrl::generateUniqueCode(6);
        ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $fullUrl,
            'expires_at' => now()->addDay(),
        ]);
        $shortUrl = url('/l/' . $shortCode);

        $balanceText .= "👉 View full history: {$shortUrl}";

        // Send DM to user
        try {
            $this->messenger->sendDirectMessage(
                $message->userId,
                OutgoingMessage::markdown($message->chatId, $balanceText)
            );

            // If command was in group, acknowledge
            if ($message->isGroupContext()) {
                $username = $message->username ? "@{$message->username}" : $message->firstName;
                $this->messenger->sendMessage(
                    OutgoingMessage::text($message->chatId, "✅ Balance sent to your DM, {$username}!")
                );
            }
        } catch (\Exception $e) {
            // Fallback if user hasn't started bot
            Log::error('Failed to send balance DM', [
                'user_id' => $message->userId,
                'error' => $e->getMessage(),
            ]);

            if ($message->isGroupContext()) {
                $this->messenger->sendMessage(
                    OutgoingMessage::text(
                        $message->chatId,
                        "❌ I couldn't send you a DM. Please use /start first to begin a conversation with me."
                    )
                );
            }
        }
    }

    private function escapeMarkdown(string $text): string
    {
        return str_replace(
            ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)', '\\~', '\\`', '\\>', '\\#', '\\+', '\\-', '\\=', '\\|', '\\{', '\\}', '\\.', '\\!'],
            $text
        );
    }

    public function getCommand(): string
    {
        return '/mybalance';
    }

    public function getAliases(): array
    {
        return ['/balance'];
    }

    public function getDescription(): string
    {
        return 'Check your points balance';
    }
}
