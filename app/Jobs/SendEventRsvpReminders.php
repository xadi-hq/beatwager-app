<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Services\EventService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendEventRsvpReminders implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     * Check for events that need RSVP reminders (24h before deadline or event)
     */
    public function handle(EventService $eventService, MessengerAdapterInterface $adapter): void
    {
        Log::info('SendEventRsvpReminders job started');

        $events = $eventService->getEventsPendingRsvpReminder();
        $remindersSent = 0;

        foreach ($events as $event) {
            try {
                // Build reminder message
                $message = "⏰ *RSVP Reminder*\n\n";
                $message .= "📅 Event: *{$event->name}*\n";

                if ($event->location) {
                    $message .= "📍 Location: {$event->location}\n";
                }

                // Convert UTC times to group timezone for display
                $eventDate = $event->group->toGroupTimezone($event->event_date);
                $message .= "🗓 Date: {$eventDate->format('D, M j @ g:i A')}\n\n";

                if ($event->rsvp_deadline) {
                    $rsvpDeadline = $event->group->toGroupTimezone($event->rsvp_deadline);
                    $message .= "⚠️ RSVP by: {$rsvpDeadline->format('M j @ g:i A')}\n\n";
                } else {
                    $message .= "📢 Please RSVP before tomorrow!\n\n";
                }

                $message .= "_Use /events to view details and RSVP_";

                // Send to group chat
                $adapter->sendMessage(new OutgoingMessage(
                    chatId: $event->group->platform_chat_id,
                    text: $message,
                    parseMode: 'Markdown'
                ));

                // Mark as sent to avoid duplicate reminders
                $event->update(['rsvp_reminder_sent_at' => now()]);

                Log::info("Sent RSVP reminder for event {$event->id}: {$event->name}");
                $remindersSent++;
            } catch (\Exception $e) {
                Log::error("Failed to send RSVP reminder for event {$event->id}: " . $e->getMessage(), [
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("SendEventRsvpReminders job completed: {$remindersSent} reminders sent");
    }
}
