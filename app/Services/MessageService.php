<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Button;
use App\DTOs\ButtonAction;
use App\DTOs\Message;
use App\DTOs\MessageContext;
use App\DTOs\MessageType;
use App\Models\Wager;
use Illuminate\Support\Collection;

/**
 * Platform-agnostic message builder (LLM-first)
 * 
 * Generates Message DTOs using LLM or fallback templates.
 */
class MessageService
{
    public function __construct(
        private readonly ContentGenerator $contentGenerator
    ) {}
    /**
     * Create wager announcement message
     */
    public function wagerAnnouncement(Wager $wager): Message
    {
        $meta = __('messages.wager.announced');
        
        $currency = $wager->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'wager.announced',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'title' => $wager->title,
                'description' => $wager->description ?? 'No description provided',
                'type' => $this->formatWagerType($wager->type),
                'stake' => $wager->stake_amount,
                'currency' => $currency,
                'betting_closes_at' => $wager->betting_closes_at->format('M j, Y g:i A'),
                'expected_settlement_at' => $wager->expected_settlement_at?->format('M j, Y g:i A'),
                'creator' => $wager->creator->name ?? 'Someone',
            ],
            group: $wager->group
        );

        $content = $this->contentGenerator->generate($ctx, $wager->group);

        // Build wager-specific buttons
        $buttons = $this->buildWagerButtons($wager);
        $buttons[] = new Button(
            label: __('messages.buttons.view_progress'),
            action: ButtonAction::Callback,
            value: "view:{$wager->id}"
        );

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],  // Already interpolated
            buttons: $buttons,
            context: $wager,
            currencyName: $currency
        );
    }

    /**
     * Create wager join announcement message with engagement triggers
     */
    public function wagerJoined(
        Wager $wager,
        \App\Models\WagerEntry $entry,
        \App\Models\User $user,
        array $engagementTriggers = []
    ): Message {
        $meta = __('messages.wager.joined');
        $currency = $wager->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'wager.joined',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $user->name,
                'wager_title' => $wager->title,
                'points_wagered' => $entry->points_wagered,
                'currency' => $currency,

                // Engagement context (DO NOT include answer - blind wagers!)
                'triggers' => $engagementTriggers,
                'total_pot' => $wager->total_points_wagered,
                'total_participants' => $wager->participants_count,
            ],
            group: $wager->group
        );

        $content = $this->contentGenerator->generate($ctx, $wager->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            context: $wager,
            currencyName: $currency
        );
    }

    /**
     * Create settlement result message
     */
    public function settlementResult(Wager $wager, Collection $winners): Message
    {
        $meta = __('messages.wager.settled');
        $currency = $wager->group->points_currency_name ?? 'points';

        // Build winners data
        $winnersData = $winners->map(fn($w) => [
            'name' => $w->user->name,
            'points_won' => $w->points_won,
        ])->toArray();

        $ctx = new MessageContext(
            key: 'wager.settled',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'title' => $wager->title,
                'outcome' => $wager->outcome_value,
                'note' => $wager->settlement_note ?? '',
                'winners' => $winnersData,
                'currency' => $currency,
            ],
            group: $wager->group
        );

        $content = $this->contentGenerator->generate($ctx, $wager->group);

        return new Message(
            content: $content,
            type: MessageType::Result,
            variables: [],
            context: $wager,
            currencyName: $currency
        );
    }

    /**
     * Create settlement reminder message
     */
    public function settlementReminder(Wager $wager, string $viewUrl): Message
    {
        $meta = __('messages.wager.reminder');

        $ctx = new MessageContext(
            key: 'wager.reminder',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'title' => $wager->title,
            ],
            group: $wager->group
        );

        $content = $this->contentGenerator->generate($ctx, $wager->group);

        $buttons = [
            new Button(
                label: __('messages.buttons.settle_wager'),
                action: ButtonAction::Url,
                value: $viewUrl
            ),
        ];

        return new Message(
            content: $content,
            type: MessageType::Reminder,
            variables: [],
            buttons: $buttons,
            context: $wager
        );
    }

    /**
     * Create view progress DM message
     */
    public function viewProgressDM(Wager $wager, string $shortUrl): Message
    {
        $template = __('messages.progress.dm_title') . __('messages.progress.dm_body');
        
        $variables = [
            'title' => $wager->title,
        ];

        $buttons = [
            new Button(
                label: __('messages.buttons.open_wager_page'),
                action: ButtonAction::Url,
                value: $shortUrl
            ),
        ];

        return new Message(
            content: $template,
            type: MessageType::Announcement,
            variables: $variables,
            buttons: $buttons,
            context: $wager
        );
    }

    /**
     * Create join confirmation message
     */
    public function joinConfirmation(): Message
    {
        return new Message(
            content: __('messages.wager.joined'),
            type: MessageType::Confirmation,
        );
    }

    /**
     * Build wager-specific buttons (Yes/No or multiple choice)
     */
    private function buildWagerButtons(Wager $wager): array
    {
        return match ($wager->type) {
            'binary' => [
                new Button(
                    label: __('messages.buttons.yes'),
                    action: ButtonAction::Callback,
                    value: "wager:{$wager->id}:yes"
                ),
                new Button(
                    label: __('messages.buttons.no'),
                    action: ButtonAction::Callback,
                    value: "wager:{$wager->id}:no"
                ),
            ],
            'multiple_choice' => collect($wager->options)
                ->map(fn($opt) => new Button(
                    label: $opt,
                    action: ButtonAction::Callback,
                    value: "wager:{$wager->id}:" . strtolower($opt)
                ))
                ->toArray(),
            default => [],
        };
    }

    /**
     * Format wager type for display
     */
    private function formatWagerType(string $type): string
    {
        return match ($type) {
            'binary' => 'Yes/No',
            'multiple_choice' => 'Multiple Choice',
            'numeric' => 'Numeric',
            'date' => 'Date',
            default => ucfirst($type),
        };
    }

    /**
     * Create point decay warning message (day 12)
     */
    public function decayWarning(string $groupName, string $currencyName, int $daysInactive, int $pointsToLose, int $currentBalance): Message
    {
        $template = "⚠️ **Point Decay Warning**\n\n" .
                   "You haven't joined any wagers in the **{group}** group for {days} days.\n\n" .
                   "🔥 Join a wager in the next 2 days or you'll lose {currency}!\n" .
                   "💸 You'll lose {points} {currency} if you remain inactive.\n\n" .
                   "Current balance: {balance} {currency}";

        $variables = [
            'group' => $groupName,
            'days' => $daysInactive,
            'points' => $pointsToLose,
            'balance' => $currentBalance,
        ];

        return new Message(
            content: $template,
            type: MessageType::Warning,
            variables: $variables,
            currencyName: $currencyName
        );
    }

    /**
     * Create point decay applied message
     */
    public function decayApplied(string $groupName, string $currencyName, int $pointsLost, int $newBalance): Message
    {
        $template = "📉 **{currency} Decayed**\n\n" .
                   "You lost **{points} {currency}** in the **{group}** group due to inactivity.\n\n" .
                   "🎯 Join a wager now to stop further decay!\n" .
                   "💰 New balance: {balance} {currency}\n\n" .
                   "_{currency} decay when you don't participate for 14+ days_";

        $variables = [
            'group' => $groupName,
            'points' => $pointsLost,
            'balance' => $newBalance,
        ];

        return new Message(
            content: $template,
            type: MessageType::Warning,
            variables: $variables,
            currencyName: $currencyName
        );
    }

    /**
     * Create event announcement message
     */
    public function eventAnnouncement(\App\Models\GroupEvent $event): Message
    {
        $meta = __('messages.event.announced');
        $currency = $event->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'event.announced',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'name' => $event->name,
                'description' => $event->description ?? '',
                'event_date' => $event->event_date->format('M j, Y g:i A'),
                'location' => $event->location ?? '',
                'attendance_bonus' => $event->attendance_bonus,
                'currency' => $currency,
                'rsvp_deadline' => $event->rsvp_deadline?->format('M j, Y'),
                'creator' => $event->creator->name ?? 'Someone',
            ],
            group: $event->group
        );

        $content = $this->contentGenerator->generate($ctx, $event->group);

        // Build RSVP buttons
        $buttons = [
            new Button(
                label: '✅ Going',
                action: ButtonAction::Callback,
                value: "event_rsvp:{$event->id}:going"
            ),
            new Button(
                label: '🤔 Maybe',
                action: ButtonAction::Callback,
                value: "event_rsvp:{$event->id}:maybe"
            ),
            new Button(
                label: '❌ Can\'t Make It',
                action: ButtonAction::Callback,
                value: "event_rsvp:{$event->id}:not_going"
            ),
        ];

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: $buttons,
            context: $event,
            currencyName: $currency
        );
    }

    /**
     * Create RSVP update announcement message
     */
    public function rsvpUpdated(\App\Models\GroupEvent $event, \App\Models\User $user, string $response, ?string $previousResponse = null): Message
    {
        // Detect if this is a change and use appropriate message type
        if ($previousResponse !== null && $previousResponse !== $response) {
            // RSVP was changed - use special change message
            $messageKey = match ($response) {
                'going' => 'messages.event.rsvp_changed_to_going',
                'maybe' => 'messages.event.rsvp_changed_to_maybe',
                'not_going' => 'messages.event.rsvp_changed_to_not_going',
            };
        } else {
            // New RSVP or no change - use regular message
            $messageKey = match ($response) {
                'going' => 'messages.event.rsvp_going',
                'maybe' => 'messages.event.rsvp_maybe',
                'not_going' => 'messages.event.rsvp_not_going',
            };
        }

        $meta = __($messageKey);
        $currency = $event->group->points_currency_name ?? 'points';

        // Map previous response to friendly text
        $previousResponseText = $previousResponse ? match ($previousResponse) {
            'going' => 'going',
            'maybe' => 'maybe',
            'not_going' => 'not going',
        } : null;

        $ctx = new MessageContext(
            key: $messageKey,
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $user->name,
                'event_name' => $event->name,
                'event_date' => $event->event_date->format('M j, Y'),
                'currency' => $currency,
                'previous_response' => $previousResponseText,
            ],
            group: $event->group
        );

        $content = $this->contentGenerator->generate($ctx, $event->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            context: $event,
            currencyName: $currency
        );
    }

    /**
     * Create attendance recorded announcement message
     */
    public function attendanceRecorded(\App\Models\GroupEvent $event, Collection $attendees, \App\Models\User $reporter): Message
    {
        $meta = __('messages.event.attendance_recorded');
        $currency = $event->group->points_currency_name ?? 'points';

        // Build attendees data
        $attendeesData = $attendees->map(fn($attendance) => [
            'name' => $attendance->user->name,
        ])->toArray();

        $attendeeNames = $attendees->map(fn($a) => $a->user->name)->join(', ', ', and ');

        $ctx = new MessageContext(
            key: 'event.attendance_recorded',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'name' => $event->name,
                'attendee_count' => $attendees->count(),
                'attendees' => $attendeeNames,
                'attendance_bonus' => $event->attendance_bonus,
                'currency' => $currency,
                'reporter' => $reporter->name,
            ],
            group: $event->group
        );

        $content = $this->contentGenerator->generate($ctx, $event->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            context: $event,
            currencyName: $currency
        );
    }
}