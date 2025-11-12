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

        // Convert UTC times to group timezone for display
        $closingTime = $wager->group->toGroupTimezone($wager->betting_closes_at);
        $settlementTime = $wager->expected_settlement_at
            ? $wager->group->toGroupTimezone($wager->expected_settlement_at)
            : null;

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
                'betting_closes_at' => $closingTime->format('M j, Y g:i A'),
                'expected_settlement_at' => $settlementTime?->format('M j, Y g:i A'),
                'creator' => $wager->creator->name ?? 'Someone',
            ],
            group: $wager->group
        );

        $content = $this->contentGenerator->generate($ctx, $wager->group);

        // Build wager-specific buttons (organized in rows)
        $buttons = $this->buildWagerButtons($wager);

        // Add Track Progress and View & Settle buttons as final row
        $buttons[] = [
            new Button(
                label: '📊 Track Progress',
                action: ButtonAction::Callback,
                value: "track_progress:{$wager->id}"
            ),
            new Button(
                label: '⚙️ View & Settle',
                action: ButtonAction::Callback,
                value: "view:{$wager->id}"
            ),
        ];

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

        // NEW: Get grudge context if it's a 1v1 wager
        $grudgeContext = null;
        if ($wager->entries->count() === 2) {
            $grudgeService = app(GrudgeService::class);
            $user1 = $wager->entries[0]->user;
            $user2 = $wager->entries[1]->user;
            $grudge = $grudgeService->getRecentHistory($user1, $user2, $wager->group);
            $grudgeContext = $grudge['narrative'];
        }

        // Build winners data
        $winnersData = $winners->map(fn($w) => [
            'name' => $w->user->name,
            'points_won' => $w->points_won,
        ])->toArray();

        // Build LLM instructions with wager scale context if applicable
        $llmInstructions = '';
        if ($wager->type === 'multiple_choice' && !empty($wager->options)) {
            $optionsStr = implode(', ', $wager->options);
            $llmInstructions = "This wager had multiple choice options: {$optionsStr}. The outcome value '{$wager->outcome_value}' refers to one of these options, NOT a score.";
        }

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
                'grudge_context' => $grudgeContext, // NEW: Pass to LLM
                'llm_instructions' => $llmInstructions, // NEW: Pass scale context to LLM
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
     * Create initial settlement reminder DM (1 hour after expected settlement)
     * Simple, respectful reminder to creator
     */
    public function settlementReminderDM(Wager $wager, string $settleUrl): Message
    {
        $meta = __('messages.wager.reminder_dm');
        $currency = $wager->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'wager.reminder_dm',
            intent: $meta['intent'] ?? 'Gentle reminder to settle wager now that result should be known',
            requiredFields: $meta['required_fields'] ?? ['title'],
            data: [
                'title' => $wager->title,
                'currency' => $currency,
            ],
            group: $wager->group
        );

        $content = $this->contentGenerator->generate($ctx, $wager->group);

        $buttons = [
            new Button(
                label: '⚙️ Settle Now',
                action: ButtonAction::Url,
                value: $settleUrl
            ),
        ];

        return new Message(
            content: $content,
            type: MessageType::Reminder,
            variables: [],
            buttons: $buttons,
            context: $wager,
            currencyName: $currency
        );
    }

    /**
     * Create escalating settlement reminder for group (24h+ after expected settlement)
     * LLM-powered with escalating urgency based on days waiting
     */
    public function settlementReminderGroup(Wager $wager, int $daysWaiting): Message
    {
        $meta = __('messages.wager.reminder_group');
        $currency = $wager->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'wager.reminder_group',
            intent: $meta['intent'] ?? 'Playful but clear group reminder to settle wager, escalating urgency with days',
            requiredFields: $meta['required_fields'] ?? ['title', 'creator', 'days_waiting', 'participant_count'],
            data: [
                'title' => $wager->title,
                'creator' => $wager->creator->name ?? 'someone',
                'days_waiting' => $daysWaiting,
                'participant_count' => $wager->participants_count,
                'currency' => $currency,
                'escalation_level' => match(true) {
                    $daysWaiting === 1 => 'gentle',      // Day 1: "Let's help them out"
                    $daysWaiting <= 3 => 'reminder',     // Days 2-3: "Still waiting"
                    $daysWaiting <= 7 => 'urgent',       // Days 4-7: "Getting old"
                    default => 'critical',                // Day 8+: "Really needs attention"
                },
            ],
            group: $wager->group
        );

        $content = $this->contentGenerator->generate($ctx, $wager->group);

        // Build settlement buttons so anyone can settle
        $buttons = $this->buildSettlementButtons($wager);

        return new Message(
            content: $content,
            type: MessageType::Reminder,
            variables: [],
            buttons: $buttons,
            context: $wager,
            currencyName: $currency
        );
    }

    /**
     * Create settlement reminder message with interactive buttons
     * Sent to group chats with direct settlement options
     */
    public function settlementReminderWithButtons(Wager $wager, bool $isGroupChat = true): Message
    {
        $meta = __('messages.wager.reminder');

        $ctx = new MessageContext(
            key: 'wager.reminder',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'title' => $wager->title,
                'is_group_chat' => $isGroupChat, // Pass context for singular/plural
            ],
            group: $wager->group
        );

        $content = $this->contentGenerator->generate($ctx, $wager->group);

        // Build wager-specific settlement buttons
        $buttons = $this->buildSettlementButtons($wager);

        return new Message(
            content: $content,
            type: MessageType::Reminder,
            variables: [],
            buttons: $buttons,
            context: $wager
        );
    }

    /**
     * Build settlement buttons based on wager type with custom labels
     */
    private function buildSettlementButtons(Wager $wager): array
    {
        $buttons = match ($wager->type) {
            'binary' => [
                [
                    new Button(
                        label: $wager->label_option_a ?? '✅ Yes',
                        action: ButtonAction::Callback,
                        value: "settle_wager:{$wager->id}:yes"
                    ),
                    new Button(
                        label: $wager->label_option_b ?? '❌ No',
                        action: ButtonAction::Callback,
                        value: "settle_wager:{$wager->id}:no"
                    ),
                ],
            ],
            'multiple_choice' => array_chunk(
                collect($wager->options)
                    ->map(fn($opt) => new Button(
                        label: $opt,
                        action: ButtonAction::Callback,
                        value: "settle_wager:{$wager->id}:" . strtolower($opt)
                    ))
                    ->toArray(),
                3 // Max 3 per row
            ),
            default => [],
        };

        return $buttons;
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
        $meta = __('messages.wager.joined');

        return new Message(
            content: $meta['fallback_template'] ?? 'Wager joined successfully!',
            type: MessageType::Confirmation,
        );
    }

    /**
     * Build wager-specific buttons (Yes/No, multiple choice, or landing page)
     */
    private function buildWagerButtons(Wager $wager): array
    {
        // Complex types require landing page
        if ($wager->requiresLandingPage()) {
            return $this->buildLandingPageButton($wager);
        }

        // Inline buttons for simple types
        $buttons = match ($wager->type) {
            'binary' => [
                new Button(
                    label: $wager->label_option_a ?? __('messages.buttons.yes'),
                    action: ButtonAction::Callback,
                    value: "wager:{$wager->id}:yes"
                ),
                new Button(
                    label: $wager->label_option_b ?? __('messages.buttons.no'),
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

        // Organize buttons into rows (max 3 per row)
        return array_chunk($buttons, 3);
    }

    /**
     * Build landing page button for complex input types
     * Uses callback pattern: click → DM with signed URL (consistent with newwager, view details, etc.)
     */
    private function buildLandingPageButton(Wager $wager): array
    {
        return [
            [
                new Button(
                    label: '📝 Join Wager',
                    action: ButtonAction::Callback,
                    value: "join_complex_wager:{$wager->id}"
                ),
            ]
        ];
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
     * Build streak context for LLM messages
     *
     * @param array $attendeesData Array of attendee data with streak info
     * @param \App\Models\EventStreakConfig|null $streakConfig
     * @param \App\Models\GroupEvent $event
     * @return array
     */
    private function buildStreakContext(array $attendeesData, $streakConfig, $event): array
    {
        if (!$streakConfig || !$streakConfig->enabled) {
            return [
                'enabled' => false,
                'top_streaks' => [],
                'milestones' => [],
                'next_event' => null,
            ];
        }

        // Identify top streaks (highest 3)
        $sortedByStreak = collect($attendeesData)
            ->sortByDesc('streak')
            ->take(3)
            ->filter(fn($a) => ($a['streak'] ?? 0) > 0)
            ->values()
            ->map(fn($a) => [
                'name' => $a['name'],
                'streak' => $a['streak'],
                'multiplier' => $a['multiplier'],
            ])
            ->toArray();

        // Identify milestone achievements (specific streaks like 5, 10, 15, 20, etc.)
        $milestoneStreaks = [5, 10, 15, 20, 25, 30, 50, 100];
        $milestones = collect($attendeesData)
            ->filter(fn($a) => in_array($a['streak'] ?? 0, $milestoneStreaks))
            ->map(fn($a) => [
                'name' => $a['name'],
                'streak' => $a['streak'],
                'multiplier' => $a['multiplier'],
            ])
            ->toArray();

        // Get next upcoming event for "keep your streak alive" messaging
        $nextEvent = \App\Models\GroupEvent::where('group_id', $event->group_id)
            ->where('status', 'upcoming')
            ->where('event_date', '>', $event->event_date)
            ->orderBy('event_date')
            ->first();

        $nextEventData = $nextEvent ? [
            'name' => $nextEvent->name,
            'date' => $nextEvent->event_date->format('M j, Y'),
            'days_until' => (int) now()->diffInDays($nextEvent->event_date),
        ] : null;

        return [
            'enabled' => true,
            'top_streaks' => $sortedByStreak,
            'milestones' => $milestones,
            'next_event' => $nextEventData,
            'llm_instructions' => 'Use streak information to create engaging, personalized announcements. ' .
                'Highlight top streaks naturally (e.g., "John\'s on a 8-event streak!"). ' .
                'Celebrate milestones enthusiastically (e.g., "Sarah just hit 10 events in a row!"). ' .
                'For next event, subtly encourage streak continuation (e.g., "Will they keep it going at [next event]?"). ' .
                'Keep it conversational and avoid being formulaic.',
        ];
    }

    /**
     * Create point decay warning message (day 12)
     */
    public function decayWarning(string $groupName, string $currencyName, int $daysInactive, int $pointsToLose, int $currentBalance): Message
    {
        $template = "⚠️ <b>Point Decay Warning</b>\n\n" .
                   "You haven't joined any wagers in the <b>{group}</b> group for {days} days.\n\n" .
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
        $template = "📉 <b>{currency} Decayed</b>\n\n" .
                   "You lost <b>{points} {currency}</b> in the <b>{group}</b> group due to inactivity.\n\n" .
                   "🎯 Join a wager now to stop further decay!\n" .
                   "💰 New balance: {balance} {currency}\n\n" .
                   "<i>{currency} decay when you don't participate for 14+ days</i>";

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

        // Get streak configuration and current user streaks
        $streakConfig = \App\Models\EventStreakConfig::where('group_id', $event->group_id)->first();
        $streaksAtRisk = [];

        if ($streakConfig && $streakConfig->enabled) {
            // Get users with active streaks who might lose them if they miss this event
            $activeStreaks = DB::table('group_user')
                ->where('group_id', $event->group_id)
                ->where('event_attendance_streak', '>', 0)
                ->orderByDesc('event_attendance_streak')
                ->take(5) // Top 5 streaks
                ->get();

            foreach ($activeStreaks as $streak) {
                $user = \App\Models\User::find($streak->user_id);
                if ($user) {
                    $streaksAtRisk[] = [
                        'name' => $user->name,
                        'streak' => $streak->event_attendance_streak,
                        'multiplier' => $streakConfig->getMultiplierForStreak($streak->event_attendance_streak),
                    ];
                }
            }
        }

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
                // NEW: Streak context for LLM
                'streaks_at_risk' => $streaksAtRisk,
                'has_active_streaks' => count($streaksAtRisk) > 0,
                'streak_instructions' => count($streaksAtRisk) > 0
                    ? 'Some members have active attendance streaks. Naturally mention that missing this event will break their streak. ' .
                      'Be conversational (e.g., "Will Sarah keep her 12-event streak alive?"). ' .
                      'Don\'t be preachy or formulaic.'
                    : null,
            ],
            group: $event->group
        );

        $content = $this->contentGenerator->generate($ctx, $event->group);

        // Build RSVP buttons (organized in rows)
        $buttons = [
            [ // Row 1: RSVP options
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
            ],
            [ // Row 2: Actions
                new Button(
                    label: '📊 Track Progress',
                    action: ButtonAction::Callback,
                    value: "track_event_progress:{$event->id}"
                ),
                new Button(
                    label: '👀 View Details',
                    action: ButtonAction::Callback,
                    value: "view_event_details:{$event->id}"
                ),
            ],
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
     * Create event cancelled announcement message
     */
    public function eventCancelled(\App\Models\GroupEvent $event): Message
    {
        $meta = __('messages.event.cancelled');
        $currency = $event->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'event.cancelled',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'event_name' => $event->name,
                'event_date' => $event->event_date->format('M j, Y g:i A'),
                'cancelled_by' => $event->creator->name ?? 'Someone',
            ],
            group: $event->group
        );

        $content = $this->contentGenerator->generate($ctx, $event->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $event,
            currencyName: $currency
        );
    }

    /**
     * Create challenge announcement message
     */
    public function challengeAnnouncement(\App\Models\Challenge $challenge): Message
    {
        $meta = __('messages.challenge.announced');
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'challenge.announced',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'title' => $challenge->description, // Using description as title
                'description' => $challenge->description,
                'reward' => $challenge->getAbsoluteAmount(), // Use absolute value for display
                'currency' => $currency,
                'deadline_at' => $challenge->completion_deadline->format('M j, Y g:i A'),
                'acceptance_deadline' => $challenge->acceptance_deadline?->format('M j, Y g:i A'),
                'creator' => $challenge->creator->name ?? 'Someone',
                'type' => $challenge->isOfferingService() ? 'offering_service' : 'offering_payment',
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        // Build challenge buttons with two-row layout
        $buttons = [
            [
                // Row 1: Accept Challenge (full width)
                new Button(
                    label: '🏃 Accept Challenge',
                    action: ButtonAction::Callback,
                    value: "challenge_accept:{$challenge->id}"
                ),
            ],
            [
                // Row 2: Track Progress and View Details (side by side)
                new Button(
                    label: '📊 Track Progress',
                    action: ButtonAction::Callback,
                    value: "track_challenge_progress:{$challenge->id}"
                ),
                new Button(
                    label: '👀 View Details',
                    action: ButtonAction::Callback,
                    value: "challenge_view:{$challenge->id}"
                ),
            ],
        ];

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [], // Already interpolated by LLM
            buttons: $buttons,
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create challenge accepted announcement message
     */
    public function challengeAccepted(\App\Models\Challenge $challenge, \App\Models\User $acceptor): Message
    {
        $meta = __('messages.challenge.accepted');
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'challenge.accepted',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $acceptor->name,
                'challenge_title' => $challenge->description,
                'reward' => $challenge->getAbsoluteAmount(),
                'currency' => $currency,
                'deadline_at' => $challenge->completion_deadline->format('M j, Y g:i A'),
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create challenge submitted announcement message
     */
    public function challengeSubmitted(\App\Models\Challenge $challenge, \App\Models\User $submitter): Message
    {
        $meta = __('messages.challenge.submitted');
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'challenge.submitted',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $submitter->name,
                'challenge_title' => $challenge->description,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create challenge approved announcement message
     */
    public function challengeApproved(\App\Models\Challenge $challenge, \App\Models\User $approver): Message
    {
        $meta = __('messages.challenge.approved');
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'challenge.approved',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $challenge->acceptor->name ?? 'Someone',
                'challenge_title' => $challenge->description,
                'reward' => $challenge->getAbsoluteAmount(),
                'currency' => $currency,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create challenge rejected announcement message
     */
    public function challengeRejected(\App\Models\Challenge $challenge, \App\Models\User $rejector): Message
    {
        $meta = __('messages.challenge.rejected');
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'challenge.rejected',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $challenge->acceptor->name ?? 'Someone',
                'challenge_title' => $challenge->description,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create challenge cancelled announcement message
     */
    public function challengeCancelled(\App\Models\Challenge $challenge, \App\Models\User $cancelledBy): Message
    {
        $meta = __('messages.challenge.cancelled');
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'challenge.cancelled',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'challenge_title' => $challenge->description,
                'cancelled_by' => $cancelledBy->name,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create challenge expired announcement message
     */
    public function challengeExpired(\App\Models\Challenge $challenge): Message
    {
        $meta = __('messages.challenge.expired');
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'challenge.expired',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'challenge_title' => $challenge->description,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create challenge deadline missed announcement message
     */
    public function challengeDeadlineMissed(\App\Models\Challenge $challenge): Message
    {
        $meta = __('messages.challenge.deadline_missed');
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'challenge.deadline_missed',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $challenge->acceptor->name ?? 'Someone',
                'challenge_title' => $challenge->description,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create SuperChallenge nudge DM (sent to randomly selected creator)
     */
    public function superChallengeNudge(\App\Models\SuperChallengeNudge $nudge): Message
    {
        $meta = __('messages.superchallenge.nudge');
        $group = $nudge->group;
        $currency = $group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'superchallenge.nudge',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'group_name' => $group->name,
                'frequency' => $nudge->group->superchallenge_frequency?->value ?? 'monthly',
            ],
            group: $group
        );

        $content = $this->contentGenerator->generate($ctx, $group);

        // Button to create the challenge (signed URL)
        $createUrl = \URL::signedRoute('superchallenge.nudge.respond', [
            'nudge' => $nudge->id,
            'action' => 'accept',
        ]);

        $declineUrl = \URL::signedRoute('superchallenge.nudge.respond', [
            'nudge' => $nudge->id,
            'action' => 'decline',
        ]);

        $buttons = [
            [
                new Button(
                    label: '✨ Create One',
                    action: ButtonAction::Url,
                    value: $createUrl
                ),
                new Button(
                    label: '❌ Not Now',
                    action: ButtonAction::Url,
                    value: $declineUrl
                ),
            ],
        ];

        return new Message(
            content: $content,
            type: MessageType::Info,
            variables: [],
            buttons: $buttons,
            context: $nudge,
            currencyName: $currency
        );
    }

    /**
     * Create SuperChallenge announcement (sent to group chat)
     */
    public function superChallengeAnnouncement(\App\Models\Challenge $challenge): Message
    {
        $meta = __('messages.superchallenge.announced');
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'superchallenge.announced',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'description' => $challenge->description,
                'prize_per_person' => $challenge->prize_per_person,
                'max_participants' => $challenge->max_participants,
                'current_participants' => $challenge->participants()->count(),
                'currency' => $currency,
                'deadline_at' => $challenge->completion_deadline->format('M j, Y g:i A'),
                'evidence_guidance' => $challenge->evidence_guidance,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        // Button to accept the challenge
        $buttons = [
            [
                new Button(
                    label: '💪 Accept Challenge',
                    action: ButtonAction::Callback,
                    value: "superchallenge_accept:{$challenge->id}"
                ),
            ],
            [
                new Button(
                    label: '📊 View Details',
                    action: ButtonAction::Callback,
                    value: "superchallenge_view:{$challenge->id}"
                ),
            ],
        ];

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: $buttons,
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create SuperChallenge acceptance notification (sent to group chat)
     */
    public function superChallengeAccepted(\App\Models\ChallengeParticipant $participant): Message
    {
        $meta = __('messages.superchallenge.accepted');
        $challenge = $participant->challenge;
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'superchallenge.accepted',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $participant->user->name,
                'description' => $challenge->description,
                'prize_per_person' => $challenge->prize_per_person,
                'current_participants' => $challenge->participants()->count(),
                'max_participants' => $challenge->max_participants,
                'currency' => $currency,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $challenge,
            currencyName: $currency
        );
    }

    /**
     * Create SuperChallenge completion claimed notification (DM to creator)
     */
    public function superChallengeCompletionClaimed(\App\Models\ChallengeParticipant $participant): Message
    {
        $meta = __('messages.superchallenge.completion_claimed');
        $challenge = $participant->challenge;
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'superchallenge.completion_claimed',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $participant->user->name,
                'description' => $challenge->description,
                'prize_per_person' => $challenge->prize_per_person,
                'currency' => $currency,
                'completed_at' => $participant->completed_at->format('M j, Y g:i A'),
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        // Buttons for validation
        $approveUrl = \URL::signedRoute('superchallenge.validate', [
            'participant' => $participant->id,
            'vote' => 'approve',
        ]);

        $rejectUrl = \URL::signedRoute('superchallenge.validate', [
            'participant' => $participant->id,
            'vote' => 'reject',
        ]);

        $buttons = [
            [
                new Button(
                    label: '✅ Approve',
                    action: ButtonAction::Url,
                    value: $approveUrl
                ),
                new Button(
                    label: '❌ Reject',
                    action: ButtonAction::Url,
                    value: $rejectUrl
                ),
            ],
        ];

        return new Message(
            content: $content,
            type: MessageType::Info,
            variables: [],
            buttons: $buttons,
            context: $participant,
            currencyName: $currency
        );
    }

    /**
     * Create SuperChallenge validation result (sent to group chat)
     */
    public function superChallengeValidated(\App\Models\ChallengeParticipant $participant, bool $approved): Message
    {
        $messageKey = $approved ? 'superchallenge.validated_approved' : 'superchallenge.validated_rejected';
        $meta = __("messages.{$messageKey}");
        $challenge = $participant->challenge;
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: $messageKey,
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $participant->user->name,
                'description' => $challenge->description,
                'prize_per_person' => $challenge->prize_per_person,
                'currency' => $currency,
                'approved' => $approved,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $participant,
            currencyName: $currency
        );
    }

    /**
     * Create SuperChallenge auto-validation notification (sent to group chat)
     */
    public function superChallengeAutoValidated(\App\Models\ChallengeParticipant $participant): Message
    {
        $meta = __('messages.superchallenge.auto_validated');
        $challenge = $participant->challenge;
        $currency = $challenge->group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'superchallenge.auto_validated',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'user_name' => $participant->user->name,
                'description' => $challenge->description,
                'prize_per_person' => $challenge->prize_per_person,
                'currency' => $currency,
                'hours_waited' => 48,
            ],
            group: $challenge->group
        );

        $content = $this->contentGenerator->generate($ctx, $challenge->group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: [],
            context: $participant,
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
                'response' => $response, // Current response (going, maybe, not_going)
                'previous_response' => $previousResponseText, // Only for change messages
            ],
            group: $event->group
        );

        // Log RSVP message generation for debugging
        \Log::info('RSVP Message Generation', [
            'message_key' => $messageKey,
            'response' => $response,
            'previous_response' => $previousResponse,
            'user_name' => $user->name,
            'event_name' => $event->name,
            'intent' => $meta['intent'],
            'tone_hints' => $meta['tone_hints'] ?? [],
        ]);

        $content = $this->contentGenerator->generate($ctx, $event->group);

        \Log::info('RSVP Message Generated Content', [
            'message_key' => $messageKey,
            'content' => $content,
        ]);

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

        // Build attendees data with streak context
        $attendeesData = $attendees->map(fn($attendance) => [
            'name' => $attendance->user->name,
            'streak' => $attendance->streak_at_time,
            'multiplier' => $attendance->multiplier_applied,
        ])->toArray();

        $attendeeNames = $attendees->map(fn($a) => $a->user->name)->join(', ', ', and ');

        // Get streak configuration for milestone detection
        $streakConfig = \App\Models\EventStreakConfig::where('group_id', $event->group_id)->first();

        // Build streak context for LLM
        $streakContext = $this->buildStreakContext($attendeesData, $streakConfig, $event);

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
                // NEW: Streak context for LLM
                'streak_context' => $streakContext,
                'has_streaks' => $streakConfig && $streakConfig->enabled,
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
     * Generate a revival message for an inactive group
     *
     * @param \App\Models\Group $group The inactive group
     * @param int $daysInactive Number of days since last activity
     * @return Message
     */
    public function revivalMessage(\App\Models\Group $group, int $daysInactive): Message
    {
        $meta = __('messages.activity.revival');
        $currency = $group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'activity.revival',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'days_inactive' => $daysInactive,
                'currency' => $currency,
                'group_name' => $group->name ?? $group->platform_chat_title,
            ],
            group: $group
        );

        $content = $this->contentGenerator->generate($ctx, $group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            context: $group,
            currencyName: $currency
        );
    }

    /**
     * Generate a season start announcement with prize structure
     *
     * @param \App\Models\Group $group
     * @param \App\Models\GroupSeason $season
     * @return Message
     */
    public function seasonStarted(\App\Models\Group $group, \App\Models\GroupSeason $season): Message
    {
        $meta = __('messages.season.started');
        $currency = $group->points_currency_name ?? 'points';

        // Format prize structure if it exists
        $prizesFormatted = '';
        if (!empty($season->prize_structure)) {
            $prizesFormatted = collect($season->prize_structure)->map(function ($prize) use ($currency) {
                $positionEmojis = [
                    'winner' => '🏆',
                    'runner_up' => '🥈',
                    'loser' => '🎭',
                    'most_active' => '⚡',
                    'most_social' => '🤝',
                    'most_servant' => '🙌',
                    'most_generous' => '💝',
                    'most_improved' => '📈',
                ];
                $emoji = $positionEmojis[$prize['position']] ?? '🎯';
                $positionName = ucwords(str_replace('_', ' ', $prize['position']));
                return "{$emoji} {$positionName}: {$prize['description']}";
            })->join("\n");
        }

        // Format end date if set
        $endsAtFormatted = $group->season_ends_at
            ? $group->toGroupTimezone($group->season_ends_at)->format('M j, Y')
            : null;

        $ctx = new MessageContext(
            key: 'season.started',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'season_number' => $season->season_number,
                'starting_balance' => $group->starting_balance,
                'currency' => $currency,
                'ends_at' => $endsAtFormatted,
                'has_prizes' => !empty($season->prize_structure),
                'prizes' => $prizesFormatted,
            ],
            group: $group
        );

        $content = $this->contentGenerator->generate($ctx, $group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            context: $season,
            currencyName: $currency
        );
    }

    /**
     * Generate a dramatic season ending announcement with recap
     *
     * @param \App\Models\Group $group
     * @param \App\Models\GroupSeason $season
     * @return Message
     */
    public function seasonEnded(\App\Models\Group $group, \App\Models\GroupSeason $season): Message
    {
        $meta = __('messages.season.ended');
        $currency = $group->points_currency_name ?? 'points';

        // Get winner and top 3
        $winner = $season->getWinner();
        $top3 = $season->getTopPlayers(3);

        // Format top 3 list
        $top3Formatted = collect($top3)->map(function ($player, $index) use ($currency) {
            $medals = ['🥇', '🥈', '🥉'];
            return "{$medals[$index]} {$player['name']}: {$player['points']} {$currency}";
        })->join("\n");

        // Format highlights
        $highlights = $season->highlights ?? [];
        $highlightsFormatted = '';

        if (!empty($highlights['biggest_win'])) {
            $win = $highlights['biggest_win'];
            $highlightsFormatted .= "💰 Biggest Win: {$win['user_name']} won {$win['amount']} {$currency} on \"{$win['wager_title']}\"\n";
        }

        if (!empty($highlights['most_active_creator'])) {
            $active = $highlights['most_active_creator'];
            $highlightsFormatted .= "🎯 Most Active: {$active['user_name']} created {$active['wagers_created']} wagers\n";
        }

        if (!empty($highlights['most_participated_wager'])) {
            $popular = $highlights['most_participated_wager'];
            $highlightsFormatted .= "🔥 Most Popular: \"{$popular['title']}\" with {$popular['participants']} participants";
        }

        // Format prize structure if it exists
        $prizesFormatted = '';
        if (!empty($season->prize_structure)) {
            $prizesFormatted = collect($season->prize_structure)->map(function ($prize) use ($currency) {
                $positionEmojis = [
                    'winner' => '🏆',
                    'runner_up' => '🥈',
                    'loser' => '🎭',
                    'most_active' => '⚡',
                    'most_social' => '🤝',
                    'most_servant' => '🙌',
                    'most_generous' => '💝',
                    'most_improved' => '📈',
                ];
                $emoji = $positionEmojis[$prize['position']] ?? '🎯';
                $positionName = ucwords(str_replace('_', ' ', $prize['position']));
                return "{$emoji} {$positionName}: {$prize['description']}";
            })->join("\n");
        }

        $ctx = new MessageContext(
            key: 'season.ended',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'season_number' => $season->season_number,
                'winner_name' => $winner['name'] ?? 'Unknown',
                'winner_points' => $winner['points'] ?? 0,
                'duration_days' => $season->getDurationInDays(),
                'total_wagers' => $season->stats['total_wagers'] ?? 0,
                'total_participants' => $season->stats['total_participants'] ?? 0,
                'top_3' => $top3Formatted,
                'highlights' => $highlightsFormatted,
                'currency' => $currency,
                'has_prizes' => !empty($season->prize_structure),
                'prizes' => $prizesFormatted,
            ],
            group: $group
        );

        $content = $this->contentGenerator->generate($ctx, $group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            context: $season,
            currencyName: $currency
        );
    }

    /**
     * Generate a scheduled message (holiday, birthday, or custom)
     *
     * @param \App\Models\Group $group The group
     * @param \App\Models\ScheduledMessage $scheduledMessage The scheduled message
     * @return Message
     */
    public function scheduledMessage(\App\Models\Group $group, \App\Models\ScheduledMessage $scheduledMessage): Message
    {
        $messageKey = "scheduled.{$scheduledMessage->message_type}";
        $meta = __("messages.{$messageKey}");
        $currency = $group->points_currency_name ?? 'points';

        // Build context data based on message type
        $data = [
            'title' => $scheduledMessage->title,
            'scheduled_date' => $scheduledMessage->scheduled_date->toFormattedDateString(),
            'currency' => $currency,
        ];

        // Add type-specific fields
        if ($scheduledMessage->message_type === 'holiday') {
            $data['holiday_name'] = $scheduledMessage->title;
        } elseif ($scheduledMessage->message_type === 'birthday') {
            $data['member_name'] = $scheduledMessage->title;
        }

        // Add custom template if provided
        if ($scheduledMessage->message_template) {
            $data['message_template'] = $scheduledMessage->message_template;
        }

        // Add custom LLM instructions if provided
        if ($scheduledMessage->llm_instructions) {
            $data['llm_instructions'] = $scheduledMessage->llm_instructions;
        }

        $ctx = new MessageContext(
            key: $messageKey,
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: $data,
            group: $group
        );

        $content = $this->contentGenerator->generate($ctx, $group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            context: $scheduledMessage,
            currencyName: $currency
        );
    }

    /**
     * Generate a birthday reminder message (7 days before birthday)
     *
     * @param \App\Models\Group $group
     * @param \App\Models\User $user
     * @param int $daysUntil
     * @return Message
     */
    public function birthdayReminder(\App\Models\Group $group, \App\Models\User $user, int $daysUntil): Message
    {
        $meta = __('messages.scheduled.birthday_reminder');
        $currency = $group->points_currency_name ?? 'points';

        $ctx = new MessageContext(
            key: 'scheduled.birthday_reminder',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'member_name' => $user->name,
                'days_until' => $daysUntil,
                'birthday_date' => $user->birthday->format('l, F j'), // "Monday, March 15"
                'currency' => $currency,
            ],
            group: $group
        );

        $content = $this->contentGenerator->generate($ctx, $group);

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            context: $user,
            currencyName: $currency
        );
    }

    /**
     * Generate custom content with LLM using a simple prompt
     * Used for ad-hoc messages that don't have structured templates
     *
     * @param \App\Models\Group $group The group context
     * @param string $prompt The generation prompt
     * @return string Generated content
     */
    public function generateWithLLM(\App\Models\Group $group, string $prompt): string
    {
        // Create a minimal MessageContext for ad-hoc generation
        $ctx = new MessageContext(
            key: 'custom.adhoc',
            intent: $prompt,
            requiredFields: [],
            data: [],
            group: $group
        );

        return $this->contentGenerator->generate($ctx, $group);
    }

    /**
     * Generate an engagement prompt for a stale wager
     *
     * @param \App\Models\Wager $wager The wager with low engagement
     * @return Message
     */
    public function engagementPrompt(Wager $wager): Message
    {
        $meta = __('messages.engagement.stale_wager');
        $currency = $wager->group->points_currency_name ?? 'points';

        // Calculate how long since wager was created
        $hoursSinceCreated = (int) $wager->created_at->diffInHours(now());

        // Calculate deadline info if available
        $deadlineHours = $wager->betting_closes_at ? (int) now()->diffInHours($wager->betting_closes_at) : null;

        // Convert UTC time to group timezone for display
        $closingTime = $wager->betting_closes_at
            ? $wager->group->toGroupTimezone($wager->betting_closes_at)
            : null;

        $ctx = new MessageContext(
            key: 'engagement.stale_wager',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'wager_title' => $wager->title,
                'hours_since_created' => $hoursSinceCreated,
                'participant_count' => $wager->participants_count,
                'stake_amount' => $wager->stake_amount,
                'currency' => $currency,
                'betting_closes_at' => $closingTime?->format('M j, Y g:i A'),
                'deadline_hours' => $deadlineHours,
            ],
            group: $wager->group
        );

        $content = $this->contentGenerator->generate($ctx, $wager->group);

        // Build wager-specific buttons (organized in rows)
        $buttons = $this->buildWagerButtons($wager);

        // Add Track Progress and View & Settle buttons as final row
        $buttons[] = [
            new Button(
                label: '📊 Track Progress',
                action: ButtonAction::Callback,
                value: "track_progress:{$wager->id}"
            ),
            new Button(
                label: '⚙️ View & Settle',
                action: ButtonAction::Callback,
                value: "view:{$wager->id}"
            ),
        ];

        return new Message(
            content: $content,
            type: MessageType::Announcement,
            variables: [],
            buttons: $buttons,
            context: $wager,
            currencyName: $currency
        );
    }
}