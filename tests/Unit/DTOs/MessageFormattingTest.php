<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use App\DTOs\Message;
use App\DTOs\MessageType;
use Tests\TestCase;

class MessageFormattingTest extends TestCase
{
    /** @test */
    public function formats_message_with_single_placeholder()
    {
        $message = new Message(
            'Hello {name}!',
            MessageType::Info,
            ['name' => 'Alice']
        );

        $this->assertEquals('Hello Alice!', $message->getFormattedContent());
    }

    /** @test */
    public function formats_message_with_multiple_placeholders()
    {
        $message = new Message(
            'Hello {name}, you have {points} {currency} remaining.',
            MessageType::Info,
            ['name' => 'Bob', 'points' => 150]
        );

        $this->assertEquals('Hello Bob, you have 150 points remaining.', $message->getFormattedContent());
    }

    /** @test */
    public function handles_repeated_placeholders()
    {
        $message = new Message(
            '{name} said "{name} is awesome!" {name} was right.',
            MessageType::Info,
            ['name' => 'Charlie']
        );

        $this->assertEquals('Charlie said "Charlie is awesome!" Charlie was right.', $message->getFormattedContent());
    }

    /** @test */
    public function leaves_unknown_placeholders_unchanged()
    {
        $message = new Message(
            'Hello {name}, you have {unknown_var} items.',
            MessageType::Info,
            ['name' => 'David']
        );

        $this->assertEquals('Hello David, you have {unknown_var} items.', $message->getFormattedContent());
    }

    /** @test */
    public function handles_numeric_values()
    {
        $message = new Message(
            'You wagered {amount} {currency} on wager #{wager_id}.',
            MessageType::Info,
            ['amount' => 250, 'wager_id' => 42]
        );

        $this->assertEquals('You wagered 250 points on wager #42.', $message->getFormattedContent());
    }

    /** @test */
    public function automatically_adds_currency_to_variables()
    {
        $message = new Message(
            'Test {currency}',
            MessageType::Info,
            ['name' => 'Alice']
        );

        $this->assertArrayHasKey('currency', $message->variables);
        $this->assertEquals('points', $message->variables['currency']);
        $this->assertEquals('Test points', $message->getFormattedContent());
    }

    /** @test */
    public function uses_custom_currency_name()
    {
        $message = new Message(
            'You have {amount} {currency}',
            MessageType::Info,
            ['amount' => 100],
            [],
            null,
            'credits'
        );

        $this->assertEquals('You have 100 credits', $message->getFormattedContent());
        $this->assertEquals('credits', $message->currencyName);
    }

    /** @test */
    public function preserves_special_characters()
    {
        $message = new Message(
            'Special: {text} - !@#$%',
            MessageType::Info,
            ['text' => 'Hello & goodbye']
        );

        $this->assertEquals('Special: Hello & goodbye - !@#$%', $message->getFormattedContent());
    }

    /** @test */
    public function handles_empty_string_value()
    {
        $message = new Message(
            'Value: "{value}"',
            MessageType::Info,
            ['value' => '']
        );

        $this->assertEquals('Value: ""', $message->getFormattedContent());
    }

    /** @test */
    public function handles_zero_value()
    {
        $message = new Message(
            'Count: {count}',
            MessageType::Info,
            ['count' => 0]
        );

        $this->assertEquals('Count: 0', $message->getFormattedContent());
    }
}
