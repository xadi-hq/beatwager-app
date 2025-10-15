<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// Helper function to generate wager creation signed URL
function generateWagerCreationUrl(
    int $telegramUserId = 12345,
    ?string $telegramUsername = 'testuser',
    ?string $telegramFirstName = 'Test',
    ?string $telegramLastName = null,
    int $telegramChatId = 67890,
    string $telegramChatType = 'group',
    ?string $telegramChatTitle = 'Test Group',
    int $expiresInMinutes = 30
): string {
    return \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'wager.create',
        now()->addMinutes($expiresInMinutes),
        [
            'u' => encrypt('telegram:' . $telegramUserId),
            'username' => $telegramUsername,
            'first_name' => $telegramFirstName,
            'last_name' => $telegramLastName,
            'chat_id' => $telegramChatId,
            'chat_type' => $telegramChatType,
            'chat_title' => $telegramChatTitle,
        ]
    );
}
