<?php

namespace App\Listeners;

use App\Events\WagerJoined;
use App\Services\EngagementTriggerService;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWagerJoinAnnouncement implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 5;

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly MessageService $messageService,
        private readonly EngagementTriggerService $triggerService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(WagerJoined $event): void
    {
        $wager = $event->wager;
        $entry = $event->entry;
        $user = $event->user;
        $group = $wager->group;

        if (!$group) {
            Log::warning('Wager joined but no group found', ['wager_id' => $wager->id]);
            return;
        }

        // Detect engagement triggers
        $triggers = $this->triggerService->detectTriggers($entry, $wager, $user, $group);

        // Generate message (includes LLM call if configured)
        $message = $this->messageService->wagerJoined($wager, $entry, $user, $triggers);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }
}
