<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AbstractCommandHandler;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\ShortUrl;
use App\Models\Wager;
use App\Services\UserMessengerService;
use Illuminate\Support\Facades\Log;

/**
 * Handle /mywagers and /mybets commands - View user's active wagers
 */
class MyWagersCommandHandler extends AbstractCommandHandler
{
    public function __construct(
        protected readonly MessengerAdapterInterface $messenger
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

        // Get user's active wagers (created or joined), sorted by deadline
        $activeWagers = Wager::where('status', 'open')
            ->where(function($query) use ($user) {
                $query->where('creator_id', $user->id)
                      ->orWhereHas('entries', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->orderBy('betting_closes_at', 'asc')
            ->limit(5)
            ->get();

        // Generate signed URL for dashboard
        $fullUrl = $this->messenger->createAuthenticatedUrl(
            'dashboard.me',
            [
                'u' => encrypt($message->platform . ':' . $message->userId),
                'focus' => 'wagers', // Which section to highlight
            ],
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

        // Format message
        $responseMessage = $this->formatWagersMessage($activeWagers, $shortUrl);

        try {
            $this->messenger->sendDirectMessage(
                $message->userId,
                OutgoingMessage::markdown($message->chatId, $responseMessage)
            );
        } catch (\Exception $e) {
            // If DM fails, tell user to start bot first
            Log::warning('Failed to send mywagers DM', [
                'user_id' => $message->userId,
                'error' => $e->getMessage(),
            ]);

            $this->messenger->sendMessage(
                OutgoingMessage::text(
                    $message->chatId,
                    "⚠️ I need to send you a private message, but you haven't started a chat with me yet.\n\n" .
                    "Use /start first, then try /mybets again!"
                )
            );
        }
    }

    private function formatWagersMessage($activeWagers, string $shortUrl): string
    {
        $count = $activeWagers->count();
        $message = "📊 *Your Active Wagers* ({$count})\n\n";

        if ($count === 0) {
            $message .= "No active wagers yet.\nUse /newwager in a group to create one!\n\n";
        } else {
            // Filter out wagers with past deadlines (should have been settled)
            $futureWagers = $activeWagers->filter(fn($w) => $w->betting_closes_at->isFuture());

            // Separate wagers into closing soon (≤7 days) and others
            $closingSoon = $futureWagers->filter(fn($w) => $w->betting_closes_at->diffInDays(now()) <= 7);
            $later = $futureWagers->filter(fn($w) => $w->betting_closes_at->diffInDays(now()) > 7);

            // Show closing soon wagers
            if ($closingSoon->isNotEmpty()) {
                $message .= "⏰ *Closing Soon:*\n";
                foreach ($closingSoon as $i => $wager) {
                    $timeLeft = $this->formatTimeLeft($wager->betting_closes_at);
                    $title = $this->truncateTitle($wager->title);
                    $message .= ($i + 1) . ". \"{$title}\" - {$timeLeft}\n";
                }
                $message .= "\n";
            }

            // Show later wagers
            if ($later->isNotEmpty()) {
                $message .= "📅 *Coming Up:*\n";
                foreach ($later as $i => $wager) {
                    $timeLeft = $this->formatTimeLeft($wager->betting_closes_at);
                    $title = $this->truncateTitle($wager->title);
                    $message .= ($i + 1) . ". \"{$title}\" - {$timeLeft}\n";
                }
                $message .= "\n";
            }

            // If there are past wagers, show count for awareness
            $pastCount = $activeWagers->count() - $futureWagers->count();
            if ($pastCount > 0) {
                $message .= "⚠️ {$pastCount} wager(s) past deadline - awaiting settlement\n\n";
            }
        }

        $message .= "👉 View all: {$shortUrl}";

        return $message;
    }

    private function formatTimeLeft(\DateTimeInterface $deadline): string
    {
        $diff = now()->diff($deadline);

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
        return strlen($title) > 40 ? substr($title, 0, 37) . '...' : $title;
    }

    public function getCommand(): string
    {
        return '/mywagers';
    }

    public function getAliases(): array
    {
        return ['/mybets'];
    }

    public function getDescription(): string
    {
        return 'View your active wagers';
    }
}
