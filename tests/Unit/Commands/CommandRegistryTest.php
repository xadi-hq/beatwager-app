<?php

declare(strict_types=1);

use App\Commands\CommandRegistry;
use App\Commands\CommandHandlerInterface;
use App\Messaging\DTOs\IncomingMessage;
use App\Messaging\DTOs\MessageType;

beforeEach(function () {
    $this->registry = new CommandRegistry();
});

test('extracts simple command without bot username', function () {
    $handler = Mockery::mock(CommandHandlerInterface::class);
    $handler->shouldReceive('getCommand')->andReturn('/start');
    $handler->shouldReceive('getAliases')->andReturn([]);
    $handler->shouldReceive('handle')->once();

    $this->registry->register($handler);

    $message = new IncomingMessage(
        platform: 'telegram',
        messageId: '123',
        type: MessageType::COMMAND,
        chatId: '456',
        userId: '789',
        username: 'testuser',
        firstName: 'Test',
        lastName: 'User',
        text: '/start',
        command: '/start',
        commandArgs: [],
        callbackData: null,
        metadata: []
    );

    $this->registry->handle($message);
});

test('extracts command with bot username suffix', function () {
    config(['telegram.bot_username' => 'WagerBot']);

    $handler = Mockery::mock(CommandHandlerInterface::class);
    $handler->shouldReceive('getCommand')->andReturn('/newwager');
    $handler->shouldReceive('getAliases')->andReturn([]);
    $handler->shouldReceive('handle')->once();

    $this->registry->register($handler);

    $message = new IncomingMessage(
        platform: 'telegram',
        messageId: '123',
        type: MessageType::COMMAND,
        chatId: '456',
        userId: '789',
        username: 'testuser',
        firstName: 'Test',
        lastName: 'User',
        text: '/newwager@WagerBot',
        command: '/newwager@WagerBot',
        commandArgs: [],
        callbackData: null,
        metadata: []
    );

    $this->registry->handle($message);
});

test('handles different bot username suffix formats', function () {
    config(['telegram.bot_username' => 'TestBot']);

    $handler = Mockery::mock(CommandHandlerInterface::class);
    $handler->shouldReceive('getCommand')->andReturn('/help');
    $handler->shouldReceive('getAliases')->andReturn([]);
    $handler->shouldReceive('handle')->once();

    $this->registry->register($handler);

    $message = new IncomingMessage(
        platform: 'telegram',
        messageId: '123',
        type: MessageType::COMMAND,
        chatId: '456',
        userId: '789',
        username: 'testuser',
        firstName: 'Test',
        lastName: 'User',
        text: '/help@TestBot',
        command: '/help@TestBot',
        commandArgs: [],
        callbackData: null,
        metadata: []
    );

    $this->registry->handle($message);
});

test('does not strip incorrect bot username', function () {
    config(['telegram.bot_username' => 'WagerBot']);

    $fallbackHandler = Mockery::mock(CommandHandlerInterface::class);
    $fallbackHandler->shouldReceive('handle')->once();

    $this->registry->setFallbackHandler($fallbackHandler);

    $message = new IncomingMessage(
        platform: 'telegram',
        messageId: '123',
        type: MessageType::COMMAND,
        chatId: '456',
        userId: '789',
        username: 'testuser',
        firstName: 'Test',
        lastName: 'User',
        text: '/newwager@DifferentBot',
        command: '/newwager@DifferentBot',
        commandArgs: [],
        callbackData: null,
        metadata: []
    );

    $this->registry->handle($message);
});
