<?php

declare(strict_types=1);

/**
 * Architecture Test: Messenger Pattern Validation
 *
 * Ensures all DM sending code follows the correct messenger pattern:
 * 1. Get MessengerBridge from MessengerFactory::for($group)
 * 2. Call getAdapter() to get the underlying adapter
 * 3. Use adapter->sendDirectMessage() with OutgoingMessage DTO
 */

test('listeners using MessengerFactory use getAdapter pattern correctly', function () {
    // Listeners use MessengerFactory::for($group) which returns MessengerBridge
    // They must call getAdapter() before sendDirectMessage()
    // (Command handlers get adapter injected via constructor, so don't need this)
    $listeners = glob(app_path('Listeners/*.php'));

    foreach ($listeners as $file) {
        $content = file_get_contents($file);

        // If listener uses MessengerFactory, it must use getAdapter()
        if (str_contains($content, 'MessengerFactory::for')) {
            expect($content)->toContain('getAdapter()');

            // Must NOT call sendDirectMessage directly on MessengerFactory result
            expect($content)->not->toMatch('/MessengerFactory::for\([^)]+\)\s*->\s*sendDirectMessage/');
        }
    }
});

test('DM sending code uses OutgoingMessage DTO', function () {
    $files = array_merge(
        glob(app_path('Listeners/*.php')),
        glob(app_path('Commands/Handlers/*.php'))
    );

    foreach ($files as $file) {
        $content = file_get_contents($file);

        if (str_contains($content, 'sendDirectMessage')) {
            expect($content)->toContain('OutgoingMessage::');
        }
    }
});

test('code does not call non-existent User sendMessage method', function () {
    $files = array_merge(
        glob(app_path('Services/*.php')),
        glob(app_path('Http/Controllers/*.php')),
        glob(app_path('Listeners/*.php')),
        glob(app_path('Commands/Handlers/*.php'))
    );

    foreach ($files as $file) {
        $content = file_get_contents($file);

        // $user->sendMessage() doesn't exist (but $group->sendMessage() does)
        expect($content)->not->toMatch('/\$user->sendMessage\(/');
    }
});

test('listeners get user telegram service before sending DM', function () {
    $listeners = glob(app_path('Listeners/*.php'));

    foreach ($listeners as $file) {
        $content = file_get_contents($file);

        // If listener sends DM, it should get user's telegram service first
        if (str_contains($content, 'sendDirectMessage')) {
            expect($content)->toMatch('/getTelegramService\(\)/');
        }
    }
});

test('listeners sending DM use platform_user_id from telegram service', function () {
    // Listeners need to get platform_user_id from user's telegram service
    // (Command handlers can use $message->userId directly from incoming message)
    $listeners = glob(app_path('Listeners/*.php'));

    foreach ($listeners as $file) {
        $content = file_get_contents($file);

        if (str_contains($content, 'sendDirectMessage')) {
            // Should use platform_user_id from telegram service
            expect($content)->toContain('platform_user_id');
        }
    }
});
