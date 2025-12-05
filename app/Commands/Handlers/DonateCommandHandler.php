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

        // Must be in DM context (not group) for privacy
        if ($message->isGroupContext()) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    '💡 Please use /donate in a direct message with me for privacy. I\'ll send you a link!'
                )
            );
            return;
        }

        // Get all groups user is a member of
        $groups = $user->groups()->get();

        if ($groups->isEmpty()) {
            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    '❌ You are not a member of any groups yet. Join a group first to send donations.'
                )
            );
            return;
        }

        // Generate signed URL for donation page (no group_id - user selects)
        $params = [
            'u' => encrypt($message->platform . ':' . $message->userId),
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

        // Send donation link in DM
        $messageText = "🎁 *Send Points to Friends*\n\n";
        $messageText .= "Choose your group, recipient, and amount:\n";
        $messageText .= "👉 {$shortUrl}\n\n";
        $messageText .= "_Link expires in 1 hour._";

        try {
            $this->messenger->sendMessage(
                OutgoingMessage::markdown($message->chatId, $messageText)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send donate link', [
                'user_id' => $message->userId,
                'error' => $e->getMessage(),
            ]);

            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "❌ Failed to send donation link. Please try again."
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
