<?php

declare(strict_types=1);

namespace App\Callbacks\Handlers;

use App\Callbacks\AbstractCallbackHandler;
use App\Events\EventRsvpUpdated;
use App\Messaging\DTOs\IncomingCallback;
use App\Models\GroupEvent;
use App\Models\GroupEventRsvp;
use App\Services\UserMessengerService;
use Illuminate\Support\Facades\Log;

/**
 * Handle event_rsvp callback - User RSVPs to a group event
 */
class EventRsvpCallbackHandler extends AbstractCallbackHandler
{
    public function handle(IncomingCallback $callback): void
    {
        // Callback data format: "event_rsvp:{event_id}:{response}"
        if (!$callback->data) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid event format',
                showAlert: true
            );
            return;
        }

        // Parse callback data
        $parts = explode(':', $callback->data);
        if (count($parts) !== 2) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid RSVP format',
                showAlert: true
            );
            return;
        }

        [$eventId, $response] = $parts;

        // Validate response
        if (!in_array($response, ['going', 'maybe', 'not_going'])) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Invalid RSVP response',
                showAlert: true
            );
            return;
        }

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

        // Check if event is still open for RSVPs
        // Use rsvp_deadline if set, otherwise use event start time
        $deadline = $event->rsvp_deadline ?? $event->event_date;
        if ($deadline < now()) {
            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ The RSVP deadline has passed',
                showAlert: true
            );
            return;
        }

        // Get or create user
        $user = UserMessengerService::findOrCreate(
            platform: $callback->platform,
            platformUserId: $callback->userId,
            userData: [
                'username' => $callback->username,
                'first_name' => $callback->firstName,
                'last_name' => $callback->lastName,
            ]
        );

        // Get the group
        $group = $event->group;

        // Ensure user is in the group
        if (!$group->users()->where('user_id', $user->id)->exists()) {
            $group->users()->attach($user->id, [
                'id' => \Illuminate\Support\Str::uuid(),
                'points' => $group->starting_balance ?? 1000,
                'role' => 'participant',
            ]);
        }

        try {
            // Check for existing RSVP to detect changes
            $existingRsvp = GroupEventRsvp::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            $previousResponse = $existingRsvp?->response;

            Log::info('RSVP Callback Processing', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'new_response' => $response,
                'previous_response' => $previousResponse,
                'had_existing_rsvp' => $existingRsvp !== null,
            ]);

            // Create or update RSVP
            $rsvp = GroupEventRsvp::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                ],
                [
                    'response' => $response,
                ]
            );

            // Response messages
            $messages = [
                'going' => '✅ You\'re going! See you there!',
                'maybe' => '🤔 Marked as maybe. Let us know when you decide!',
                'not_going' => '❌ Sorry you can\'t make it. Maybe next time!',
            ];

            // Send success message
            $this->messenger->answerCallback(
                $callback->callbackId,
                $messages[$response],
                showAlert: false
            );

            // Refresh the event to ensure listener gets latest data
            $event->refresh();

            // Dispatch event for RSVP announcement with previous response
            EventRsvpUpdated::dispatch($event, $user, $response, $previousResponse);

        } catch (\Exception $e) {
            Log::error('Error handling event RSVP', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
                'user_id' => $callback->userId,
                'response' => $response,
            ]);

            $this->messenger->answerCallback(
                $callback->callbackId,
                '❌ Error processing RSVP: ' . $e->getMessage(),
                showAlert: true
            );
        }
    }

    public function getAction(): string
    {
        return 'event_rsvp';
    }

    public function getDescription(): string
    {
        return 'RSVP to a group event';
    }
}
