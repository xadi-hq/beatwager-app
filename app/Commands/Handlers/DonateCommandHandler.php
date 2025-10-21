<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\Group;
use App\Models\ShortUrl;
use App\Services\UserMessengerService;
use Illuminate\Support\Facades\Log;

/**
 * Handle /donate command - Send points to another user
 */
class DonateCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        MessengerAdapterInterface $messenger
    ) {
        parent::__construct($messenger);
    }

    public function handle(IncomingMessage $message): void
    {
        // Find or create user
        $user = UserMessengerService::findOrCreate(
            platform: $message->platform,
            platformUserId: $message->userId,
            userData: [
                'username' => $message->username,
                'first_name' => $message->firstName,
                'last_name' => $message->lastName,
            ]
        );

        // Must be in group context
        if (!$message->isGroupContext()) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    '❌ Donations can only be sent from within a group.'
                )
            );
            return;
        }

        // Find group
        $group = Group::where('platform', $message->platform)
            ->where('platform_chat_id', $message->chatId)
            ->first();

        if (!$group) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    '❌ This group is not registered in BeatWager.'
                )
            );
            return;
        }

        // Verify user is a member
        $groupUser = $group->users()->where('users.id', $user->id)->first();

        if (!$groupUser) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    '❌ You are not a member of this group.'
                )
            );
            return;
        }

        // Generate signed URL for donation page
        $params = [
            'u' => encrypt($message->platform . ':' . $message->userId),
            'group_id' => $group->id,
        ];

        $fullUrl = $this->messenger->createAuthenticatedUrl(
            'donations.create',
            $params,
            60 // 1 hour expiry
        );

        // Create short URL
        $shortCode = ShortUrl::generateUniqueCode(6);
        ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $fullUrl,
            'expires_at' => now()->addHour(),
        ]);
        $shortUrl = url('/l/' . $shortCode);

        // Send DM to user with donation link
        $currencyName = $group->points_currency_name ?? 'points';
        $groupName = $this->escapeMarkdown($group->name);

        $messageText = "🎁 *Send {$currencyName} in {$groupName}*\n\n";
        $messageText .= "Choose a recipient and amount:\n";
        $messageText .= "👉 {$shortUrl}\n\n";
        $messageText .= "_No minimum amount required\\. Link expires in 1 hour\\._";

        try {
            $this->messenger->sendDirectMessage(
                $message->userId,
                OutgoingMessage::markdown($message->chatId, $messageText)
            );

            // Acknowledge in group
            $username = $message->username ? "@{$message->username}" : $message->firstName;
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "✅ Donation page sent to your DM, {$username}!"
                )
            );
        } catch (\Exception $e) {
            // Fallback if user hasn't started bot
            Log::error('Failed to send donate DM', [
                'user_id' => $message->userId,
                'error' => $e->getMessage(),
            ]);

            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "❌ I couldn't send you a DM. Please use /start first to begin a conversation with me."
                )
            );
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
        return '/donate';
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getDescription(): string
    {
        return 'Send points to another member';
    }
}
