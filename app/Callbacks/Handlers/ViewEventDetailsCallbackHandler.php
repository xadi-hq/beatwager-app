<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Messaging\DTOs\IncomingCallback;
use App\Messaging\DTOs\OutgoingMessage;
use App\Models\GroupEvent;
use App\Models\ShortUrl;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Handle view_event_details callback - User wants to view event details with signed URL
 */
class ViewEventDetailsCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "view_event_details:{event_id}"
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
        $event = GroupEvent::find($eventId);
        if (!$event) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Event not found',
                showAlert: true
            );
            return;
        }

        // Generate signed URL with encrypted user ID
        $signedUrl = URL::temporarySignedRoute(
            'events.show',
            now()->addDays(30),
            [
                'event' => $eventId,
                'u' => encrypt($callback->platform . ':' . $callback->userId)
            ]
        );

        // Create short URL
        $shortCode = ShortUrl::generateUniqueCode(6);
        ShortUrl::create([
            'code' => $shortCode,
            'target_url' => $signedUrl,
            'expires_at' => now()->addDays(30),
        ]);

        // Build short URL
        $shortUrlFull = url('/l/' . $shortCode);

        // Answer callback query
        $this->messenger->answerCallback(
            $callback->callbackId,
            '📅 Opening event details...'
        );

        // Send short URL as direct message to user
        $dmMessage = "📅 *View Event Details*\n\n";
        $dmMessage .= "🎉 Event: {$event->name}\n\n";
        $dmMessage .= "Click to view full details and RSVP list:\n";
        $dmMessage .= $shortUrlFull;

        try {
            $this->messenger->sendDirectMessage(
                $callback->userId,
                OutgoingMessage::markdown($callback->chatId, $dmMessage)
            );
        } catch (\Exception $e) {
            // If DM fails, log warning
            Log::warning('Failed to send event view DM', [
                'user_id' => $callback->userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getAction(): string
    {
        return 'view_event_details';
    }

    public function getDescription(): string
    {
        return 'View detailed event information with RSVP list';
    }
}
