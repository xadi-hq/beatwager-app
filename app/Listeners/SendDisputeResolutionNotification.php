<?php

namespace App\Listeners;

use App\Events\DisputeResolved;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendDisputeResolutionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 5;

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(DisputeResolved $event): void
    {
        $dispute = $event->dispute;
        $group = $dispute->group;

        if (!$group) {
            Log::warning('Dispute resolved but no group found', ['dispute_id' => $dispute->id]);
            return;
        }

        // Generate message for dispute resolution
        $message = $this->messageService->disputeResolved($dispute);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }
}
