<?php

namespace App\Listeners;

use App\Events\DisputeCreated;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendDisputeNotification implements ShouldQueue
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
    public function handle(DisputeCreated $event): void
    {
        $dispute = $event->dispute;
        $group = $dispute->group;

        if (!$group) {
            Log::warning('Dispute created but no group found', ['dispute_id' => $dispute->id]);
            return;
        }

        // Generate message for dispute creation
        $message = $this->messageService->disputeCreated($dispute);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }
}
