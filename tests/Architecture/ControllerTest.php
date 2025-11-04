<?php

declare(strict_types=1);

/**
 * Architecture Test: Controller Return Types
 *
 * Ensures all controllers follow consistent return type patterns,
 * preventing type mismatches like RedirectResponse vs Response.
 */

arch('controllers use strict types')
    ->expect('App\Http\Controllers')
    ->toUseStrictTypes();

test('controllers using Inertia location return Response not RedirectResponse', function () {
    $controllers = glob(app_path('Http/Controllers/*.php'));

    foreach ($controllers as $controllerFile) {
        $content = file_get_contents($controllerFile);

        // Find methods that use Inertia::location
        if (preg_match_all('/public function (\w+)\([^)]*\):\s*(\w+(?:\|\w+)?)\s*{[^}]*Inertia::location/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $methodName = $match[1];
                $returnType = $match[2];

                // Inertia::location returns Response, not RedirectResponse
                expect($returnType)
                    ->not->toBe('RedirectResponse',
                        "Method {$methodName} in " . basename($controllerFile) . " uses Inertia::location but declares return type {$returnType}");
            }
        }
    }
});

test('controllers do not invent non-existent model methods', function () {
    $controllers = glob(app_path('Http/Controllers/*.php'));

    $forbiddenMethods = [
        'User::sendMessage',
        'User::sendDirectMessage',
        'Group::sendMessage',
    ];

    foreach ($controllers as $controllerFile) {
        $content = file_get_contents($controllerFile);

        foreach ($forbiddenMethods as $method) {
            expect($content)
                ->not->toContain($method,
                    "Controller " . basename($controllerFile) . " should not call {$method} - this method doesn't exist");
        }
    }
});
