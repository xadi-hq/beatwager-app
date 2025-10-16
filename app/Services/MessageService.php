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
        
        $ctx = new MessageContext(
            key: 'wager.announced',
            intent: $meta['intent'],
            requiredFields: $meta['required_fields'],
            data: [
                'title' => $wager->title,
                'description' => $wager->description ?? 'No description provided',
                'type' => $this->formatWagerType($wager->type),
                'stake' => $wager->stake_amount,
                'deadline' => $wager->deadline->format('M j, Y g:i A'),
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
            currencyName: $wager->group->points_currency_name ?? 'points'
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
}