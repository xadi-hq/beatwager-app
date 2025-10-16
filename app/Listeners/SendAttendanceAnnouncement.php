<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendAttendanceAnnouncement implements ShouldQueue
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
    public function handle(AttendanceRecorded $event): void
    {
        $groupEvent = $event->event;
        $reporter = $event->reporter;
        $group = $groupEvent->group;

        if (!$group) {
            Log::warning('Attendance recorded but no group found', ['event_id' => $groupEvent->id]);
            return;
        }

        // Get attendees
        $attendees = $groupEvent->attendance()
            ->where('attended', true)
            ->with('user')
            ->get();

        // Generate message (includes LLM call if configured)
        $message = $this->messageService->attendanceRecorded($groupEvent, $attendees, $reporter);

        // Send to group's messenger platform
        $group->sendMessage($message);
    }
}
