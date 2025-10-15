<?php

namespace Tests\Unit\Messengers;

use App\DTOs\Message;
use App\DTOs\MessageType;
use App\Services\Messengers\TelegramMessenger;
use Tests\TestCase;

class TelegramMessengerFormattingTest extends TestCase
{
    private TelegramMessenger $messenger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messenger = new TelegramMessenger();
    }

    public function test_converts_markdown_bold_to_html(): void
    {
        $message = new Message(
            content: '🔥 Big news! **Xander** just joined the **Feyenoord - Panathinaikos** wager with **100 points**!',
            type: MessageType::Announcement,
        );

        $formatted = $this->messenger->formatMessage($message);

        $this->assertStringNotContainsString('**', $formatted);
        $this->assertStringContainsString('<b>Xander</b>', $formatted);
        $this->assertStringContainsString('<b>Feyenoord - Panathinaikos</b>', $formatted);
        $this->assertStringContainsString('<b>100 points</b>', $formatted);
    }

    public function test_converts_markdown_italic_to_html(): void
    {
        $message = new Message(
            content: 'This is *important* and this is _also important_',
            type: MessageType::Announcement,
        );

        $formatted = $this->messenger->formatMessage($message);

        $this->assertStringContainsString('<i>important</i>', $formatted);
        $this->assertStringContainsString('<i>also important</i>', $formatted);
    }

    public function test_converts_markdown_code_to_html(): void
    {
        $message = new Message(
            content: 'Run the command `php artisan test` to verify',
            type: MessageType::Announcement,
        );

        $formatted = $this->messenger->formatMessage($message);

        $this->assertStringContainsString('<code>php artisan test</code>', $formatted);
    }

    public function test_bolds_key_value_pairs(): void
    {
        $message = new Message(
            content: "Question: Will it rain?\nDeadline: Tomorrow\nStake: 50 points",
            type: MessageType::Announcement,
        );

        $formatted = $this->messenger->formatMessage($message);

        $this->assertStringContainsString('<b>Question:</b>', $formatted);
        $this->assertStringContainsString('<b>Deadline:</b>', $formatted);
        $this->assertStringContainsString('<b>Stake:</b>', $formatted);
    }

    public function test_handles_multiline_markdown(): void
    {
        $message = new Message(
            content: "First line with **bold text**\nSecond line with *italic*\nThird line with `code`",
            type: MessageType::Announcement,
        );

        $formatted = $this->messenger->formatMessage($message);

        $this->assertStringContainsString('<b>bold text</b>', $formatted);
        $this->assertStringContainsString('<i>italic</i>', $formatted);
        $this->assertStringContainsString('<code>code</code>', $formatted);
    }

    public function test_does_not_double_escape_already_formatted_html(): void
    {
        $message = new Message(
            content: 'Already formatted: <b>bold</b> and <i>italic</i>',
            type: MessageType::Announcement,
        );

        $formatted = $this->messenger->formatMessage($message);

        // Should preserve existing HTML tags
        $this->assertStringContainsString('<b>bold</b>', $formatted);
        $this->assertStringContainsString('<i>italic</i>', $formatted);
    }

    public function test_handles_empty_message(): void
    {
        $message = new Message(
            content: '',
            type: MessageType::Announcement,
        );

        $formatted = $this->messenger->formatMessage($message);

        $this->assertSame('', $formatted);
    }

    public function test_preserves_emojis_and_special_characters(): void
    {
        $message = new Message(
            content: '🔥 **Hot** take! 💪 Let\'s go! 🎯',
            type: MessageType::Announcement,
        );

        $formatted = $this->messenger->formatMessage($message);

        $this->assertStringContainsString('🔥', $formatted);
        $this->assertStringContainsString('💪', $formatted);
        $this->assertStringContainsString('🎯', $formatted);
        $this->assertStringContainsString('<b>Hot</b>', $formatted);
    }
}
