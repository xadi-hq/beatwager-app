<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Models\GroupEvent;
use Illuminate\Support\Facades\Log;

/**
 * Handle track_event_progress callback - User wants to track event RSVP progress
 */
class TrackEventProgressCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "track_event_progress:{event_id}"
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid event format',
                showAlert: true
            );
            return;
        }

        $eventId = $callback->data;

        // Find the event
        $event = GroupEvent::with(['group', 'rsvps.user'])->find($eventId);
        if (!$event) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Event not found',
                showAlert: true
            );
            return;
        }

        try {
            // Build event progress message
            $message = $this->buildProgressMessage($event);

            // Send the message
            $this->messenger->sendMessage(
                OutgoingMessage::markdown($callback->chatId, $message)
            );

            // Answer the callback
            $this->messenger->answerCallback(
                $callback->callbackId,
                '✅ Progress sent',
                showAlert: false
            );

        } catch (\Exception $e) {
            Log::error('Error tracking event progress', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
                'user_id' => $callback->userId,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error loading event progress',
                showAlert: true
            );
        }
    }

    /**
     * Build event progress message
     */
    private function buildProgressMessage(GroupEvent $event): string
    {
        $group = $event->group;
        $currencyName = $group->points_currency_name ?? 'points';

        $message = "📅 *Event Progress*\n\n";
        $message .= "*{$event->name}*\n\n";

        if ($event->description) {
            $message .= "_{$event->description}_\n\n";
        }

        $message .= "*Status:* " . ucfirst($event->status) . "\n";
        $message .= "*When:* " . $event->event_date->format('M j, Y g:i A') . "\n";

        if ($event->location) {
            $message .= "*Where:* {$event->location}\n";
        }

        if ($event->attendance_bonus) {
            $message .= "*Attendance Bonus:* {$event->attendance_bonus} {$currencyName}\n";
        }

        // RSVP deadline
        if ($event->rsvp_deadline) {
            $deadline = $event->rsvp_deadline;
            if ($deadline > now()) {
                $message .= "*RSVP by:* " . $deadline->format('M j, Y') . "\n";
            }
        }

        // RSVP counts
        $goingCount = $event->rsvps->where('response', 'going')->count();
        $maybeCount = $event->rsvps->where('response', 'maybe')->count();
        $notGoingCount = $event->rsvps->where('response', 'not_going')->count();
        $totalRsvps = $event->rsvps->count();

        $message .= "\n*RSVPs:* {$totalRsvps} total\n";
        $message .= "• ✅ Going: {$goingCount}\n";
        $message .= "• 🤔 Maybe: {$maybeCount}\n";
        $message .= "• ❌ Not Going: {$notGoingCount}";

        return $message;
    }

    public function getAction(): string
    {
        return 'track_event_progress';
    }

    public function getDescription(): string
    {
        return 'Track event RSVP progress';
    }
}
