# Platform-Agnostic Authentication

## Overview

BeatWager uses a **platform-agnostic authentication system** that works with any messaging platform (Telegram, Discord, Slack, etc.). The system follows Laravel best practices:

1. **Platform sends signed URL** → User clicks link
2. **Middleware validates signature** → Establishes Laravel session
3. **User navigates freely** → Session handles auth (clean URLs, no tokens)

## Architecture

```
┌─────────────────────────────────────────┐
│  Any Platform (Telegram/Discord/Slack)  │
│  Generates signed URL:                  │
│  /wager/123?u=encrypted_id&signature=…  │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  AuthenticateFromSignedUrl Middleware   │
│  (Platform-agnostic)                    │
│                                         │
│  1. User has session? → Continue        │
│  2. Valid signed URL? → Auth & session  │
│  3. Neither? → 401 Unauthorized         │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  Laravel Session Established            │
│  - Clean URLs (no tokens)               │
│  - Works across all routes              │
│  - Persists 24 hours (remember me)      │
└─────────────────────────────────────────┘
```

## How It Works

### First Visit (From Any Platform)

**Example**: User clicks Telegram bot link
```
https://app.com/wager/123?u=eyJpdiI6...&expires=...&signature=...
```

1. **Middleware intercepts request**
2. **Validates signature** using Laravel's `$request->hasValidSignature()`
3. **Decrypts user identifier** from `u` parameter
4. **Finds user** via `findUserByIdentifier()` (platform-agnostic lookup)
5. **Establishes session** via `Auth::login($user, true)`
6. **Redirects to clean URL**: `https://app.com/wager/123`

### Subsequent Navigation

**Example**: User clicks "Back to Dashboard"
```
https://app.com/me
```

1. **Middleware checks session** → Valid ✓
2. **User continues** → No authentication needed
3. **Clean URL** → Bookmarkable, shareable

## Platform Detection Strategy

The middleware uses a **fallback approach** to find users:

```php
private function findUserByIdentifier($userId, Request $request): ?User
{
    // Try Telegram (numeric ID)
    if (is_numeric($userId)) {
        $user = User::where('telegram_id', $userId)->first();
        if ($user) return $user;
    }

    // Future: Discord (snowflake IDs - also numeric but larger)
    // Could use explicit platform parameter: ?platform=discord

    // Future: Slack (alphanumeric IDs)
    // if (str_starts_with($userId, 'U')) {
    //     return User::where('slack_id', $userId)->first();
    // }

    return null;
}
```

### Future Platform Support

When adding Discord/Slack:

**Option 1: ID Format Detection** (Current approach)
```php
// Discord snowflakes are 17-19 digit numbers
if (is_numeric($userId) && strlen($userId) > 15) {
    return User::where('discord_id', $userId)->first();
}

// Slack IDs start with 'U' or 'W'
if (preg_match('/^[UW][A-Z0-9]+$/', $userId)) {
    return User::where('slack_id', $userId)->first();
}
```

**Option 2: Explicit Platform Parameter** (More explicit)
```php
$platform = $request->query('platform', 'telegram');

match($platform) {
    'telegram' => User::where('telegram_id', $userId)->first(),
    'discord' => User::where('discord_id', $userId)->first(),
    'slack' => User::where('slack_id', $userId)->first(),
    default => null
};
```

## Generating Signed URLs (Platform-Agnostic)

Each platform's webhook handler generates signed URLs using **Laravel's built-in signing**:

```php
// Telegram example (in TelegramWebhookController)
$wagerUrl = URL::temporarySignedRoute(
    'wager.show',
    now()->addHours(24),
    [
        'wager' => $wager->id,
        'u' => encrypt($user->telegram_id)  // Platform-specific ID
    ]
);

// Future Discord example (in DiscordWebhookController)
$wagerUrl = URL::temporarySignedRoute(
    'wager.show',
    now()->addHours(24),
    [
        'wager' => $wager->id,
        'u' => encrypt($user->discord_id),  // Different platform, same pattern
        'platform' => 'discord'              // Optional explicit platform
    ]
);
```

**Key Points**:
- Same route, same middleware
- Only the encrypted user ID changes
- Platform detection is automatic (or explicit via `?platform=`)

## Security Considerations

### Signed URL Security
- ✅ **Signature validation** prevents tampering
- ✅ **Time-based expiry** (24 hours default)
- ✅ **Encrypted user IDs** using Laravel's encryption
- ✅ **One-time use** (establishes session, then clean URL)

### Session Security
- ✅ **Encrypted session data** (Laravel default)
- ✅ **httpOnly cookies** (not accessible via JavaScript)
- ✅ **Secure flag** (HTTPS only in production)
- ✅ **SameSite=Lax** (CSRF protection)
- ✅ **Remember me** (24-hour persistent sessions)

### Platform Isolation
- ✅ Each platform has separate user ID column
- ✅ Single user can link multiple platforms (future feature)
- ✅ Platform detection doesn't rely on user input

## Benefits Over Platform-Specific Approach

### Scalability
✅ Add new platforms without changing middleware
✅ Shared authentication logic
✅ Consistent user experience across platforms

### Maintainability
✅ Single source of truth for auth logic
✅ No code duplication per platform
✅ Easy to test and debug

### User Experience
✅ Clean URLs after first visit
✅ Fast navigation (session-based)
✅ Bookmarkable pages
✅ Works across browser tabs

## Code Structure

```
app/Http/Middleware/
└── AuthenticateFromSignedUrl.php    ← Platform-agnostic auth

app/Http/Controllers/Api/
├── TelegramWebhookController.php    ← Generates Telegram signed URLs
├── DiscordWebhookController.php     ← Future: Discord signed URLs
└── SlackWebhookController.php       ← Future: Slack signed URLs

routes/
└── web.php                          ← Routes use 'signed.auth' middleware

bootstrap/
└── app.php                          ← Middleware registration
```

## Example: Adding Discord Support

**Step 1: Database Migration**
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('discord_id')->nullable()->unique()->after('telegram_id');
    $table->string('discord_username')->nullable()->after('discord_id');
});
```

**Step 2: Update Middleware** (Automatic ID detection)
```php
// In AuthenticateFromSignedUrl::findUserByIdentifier()
if (is_numeric($userId)) {
    // Try Telegram first (smaller numbers)
    if ($userId < 10000000000) {
        return User::where('telegram_id', $userId)->first();
    }
    // Try Discord (larger snowflake IDs)
    return User::where('discord_id', $userId)->first();
}
```

**Step 3: Create Discord Webhook Controller**
```php
class DiscordWebhookController extends Controller
{
    public function handleCommand(Request $request)
    {
        $userId = $request->input('user.id');
        $user = User::firstOrCreate(
            ['discord_id' => $userId],
            ['discord_username' => $request->input('user.username')]
        );

        // Generate signed URL (same pattern as Telegram!)
        $dashboardUrl = URL::temporarySignedRoute(
            'dashboard.me',
            now()->addHours(24),
            ['u' => encrypt($user->discord_id)]
        );

        // Send response to Discord...
    }
}
```

**Step 4: Done!** No changes needed to:
- Middleware (already platform-agnostic)
- Routes (same routes work for all platforms)
- Frontend (same Vue components)

## Testing

### Manual Testing
1. Generate signed URL from platform
2. Click link → Should redirect to clean URL
3. Navigate to other pages → Should work without auth
4. Close browser → Reopen → Session persists (24 hours)
5. Wait for session expiry → Should require new signed URL

### Automated Testing
```php
// Test signed URL authentication
public function test_signed_url_establishes_session()
{
    $user = User::factory()->create(['telegram_id' => 123456]);

    $signedUrl = URL::temporarySignedRoute(
        'dashboard.me',
        now()->addHours(1),
        ['u' => encrypt($user->telegram_id)]
    );

    $response = $this->get($signedUrl);

    $response->assertRedirect('/me'); // Clean URL
    $this->assertAuthenticatedAs($user);
}

// Test session persistence
public function test_session_allows_navigation()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/me');
    $response->assertStatus(200);

    $response = $this->get('/wager/create');
    $response->assertStatus(200); // No re-authentication needed
}
```

## Migration Notes

- ✅ **Backward compatible**: Existing Telegram signed URLs still work
- ✅ **No database changes**: Uses existing session infrastructure
- ✅ **No breaking changes**: Frontend unchanged
- ✅ **Incremental rollout**: Add platforms one at a time

## Future Enhancements

1. **Multi-platform linking**: Single user, multiple platform accounts
2. **Platform preferences**: User chooses preferred notification platform
3. **Cross-platform sync**: Activity visible across all linked platforms
4. **Logout endpoint**: `/logout` to invalidate sessions
5. **Session management UI**: View/revoke active sessions
