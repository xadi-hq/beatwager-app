<?php

namespace Tests\Unit\Messengers;

use App\DTOs\Button;
use App\DTOs\ButtonAction;
use App\Services\Messengers\TelegramMessenger;
use Tests\TestCase;

class TelegramMessengerButtonTest extends TestCase
{
    private TelegramMessenger $messenger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messenger = new TelegramMessenger();
    }

    public function test_builds_buttons_from_flat_array(): void
    {
        $buttons = [
            new Button('Yes', ButtonAction::Callback, 'wager:1:yes'),
            new Button('No', ButtonAction::Callback, 'wager:1:no'),
        ];

        $keyboard = $this->messenger->buildButtons($buttons);

        $this->assertNotNull($keyboard);
        $this->assertInstanceOf(\TelegramBot\Api\Types\Inline\InlineKeyboardMarkup::class, $keyboard);
    }

    public function test_builds_buttons_from_pre_organized_rows(): void
    {
        $buttons = [
            [
                new Button('Option A', ButtonAction::Callback, 'wager:1:a'),
                new Button('Option B', ButtonAction::Callback, 'wager:1:b'),
            ],
            [
                new Button('Track Progress', ButtonAction::Callback, 'track:1'),
                new Button('View & Settle', ButtonAction::Url, 'https://example.com'),
            ],
        ];

        $keyboard = $this->messenger->buildButtons($buttons);

        $this->assertNotNull($keyboard);
        $this->assertInstanceOf(\TelegramBot\Api\Types\Inline\InlineKeyboardMarkup::class, $keyboard);
    }

    public function test_handles_empty_button_array(): void
    {
        $buttons = [];

        $keyboard = $this->messenger->buildButtons($buttons);

        $this->assertNull($keyboard);
    }

    public function test_handles_mixed_button_types(): void
    {
        $buttons = [
            [
                new Button('Callback Button', ButtonAction::Callback, 'callback:data'),
                new Button('URL Button', ButtonAction::Url, 'https://example.com'),
            ],
        ];

        $keyboard = $this->messenger->buildButtons($buttons);

        $this->assertNotNull($keyboard);
        $this->assertInstanceOf(\TelegramBot\Api\Types\Inline\InlineKeyboardMarkup::class, $keyboard);
    }
}
