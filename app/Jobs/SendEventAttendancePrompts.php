<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\EventService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendEventAttendancePrompts implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     * Check for events that need attendance prompts and send them
     */
    public function handle(EventService $eventService): void
    {
        Log::info('SendEventAttendancePrompts job started');

        $events = $eventService->getEventsPendingAttendancePrompt();
        $promptsSent = 0;

        foreach ($events as $event) {
            try {
                // TODO: Send prompt via messaging service
                Log::info("Attendance prompt needed for event {$event->id}: {$event->name}");
                $promptsSent++;
            } catch (\Exception $e) {
                Log::error("Failed to send attendance prompt for event {$event->id}: " . $e->getMessage());
            }
        }

        Log::info("SendEventAttendancePrompts job completed: {$promptsSent} prompts sent");
    }
}
