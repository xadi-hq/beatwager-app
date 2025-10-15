<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Button;
use App\DTOs\ButtonAction;
use App\DTOs\Message;
use App\DTOs\MessageType;
use App\Models\Wager;
use Illuminate\Support\Collection;

/**
 * Platform-agnostic message builder
 * 
 * Generates Message DTOs from templates for any messenger platform.
 * Does NOT contain platform-specific formatting (HTML, Markdown, etc.)
 */
class MessageService
{
    /**
     * Create wager announcement message
     */
    public function wagerAnnouncement(Wager $wager): Message
    {
        $template = __('messages.wager.announced');
        
        $variables = [
            'title' => $wager->title,
            'description' => $wager->description ?? 'No description provided',
            'type' => $this->formatWagerType($wager->type),
            'stake' => $wager->stake_amount,
            'deadline' => $wager->deadline->format('M j, Y g:i A'),
        ];

        // Build wager-specific buttons (Yes/No or multiple choice)
        $buttons = $this->buildWagerButtons($wager);
        
        // Add View Progress button on separate row
        $buttons[] = new Button(
            label: __('messages.buttons.view_progress'),
            action: ButtonAction::Callback,
            value: "view:{$wager->id}"
        );

        return new Message(
            content: $template,
            type: MessageType::Announcement,
            variables: $variables,
            buttons: $buttons,
            context: $wager
        );
    }

    /**
     * Create settlement result message
     */
    public function settlementResult(Wager $wager, Collection $winners): Message
    {
        $template = __('messages.wager.settled');
        
        $variables = [
            'title' => $wager->title,
            'outcome' => $wager->outcome_value,
            'note' => $wager->settlement_note ? "\nNote: {$wager->settlement_note}" : '',
        ];

        // Build winners list
        $winnersText = __('messages.winners.header');
        if ($winners->count() > 0) {
            foreach ($winners as $winner) {
                $winnersText .= str_replace(
                    ['{name}', '{points}'],
                    [$winner->user->name, $winner->points_won],
                    __('messages.winners.single')
                );
            }
        } else {
            $winnersText .= __('messages.winners.none');
        }

        $variables['note'] .= "\n" . $winnersText;

        return new Message(
            content: $template,
            type: MessageType::Result,
            variables: $variables,
            context: $wager
        );
    }

    /**
     * Create settlement reminder message
     */
    public function settlementReminder(Wager $wager, string $viewUrl): Message
    {
        $template = __('messages.wager.reminder');
        
        $variables = [
            'title' => $wager->title,
        ];

        $buttons = [
            new Button(
                label: __('messages.buttons.settle_wager'),
                action: ButtonAction::Url,
                value: $viewUrl
            ),
        ];

        return new Message(
            content: $template,
            type: MessageType::Reminder,
            variables: $variables,
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
}