# Session-Based Authentication Implementation

## Problem Statement

The previous implementation had several issues:
1. **Browser Performance**: Memory leak from uncleaned `setInterval` causing browser freeze
2. **Long, Ugly URLs**: Every navigation included tokens/signatures in URLs
3. **Poor UX**: URLs weren't shareable or bookmarkable
4. **Token Generation Overhead**: Every page load generated new tokens
5. **Not Best Practice**: Not using Laravel's built-in session authentication

## Solution: Laravel Session Authentication

Implemented proper Laravel session-based authentication following best practices:

### Architecture

```
┌─────────────────┐
│  Telegram Bot   │
│  sends link     │
└────────┬────────┘
         │ with token or signed URL
         ▼
┌─────────────────────────────────┐
│  AuthenticateTelegramUser       │
│  Middleware                     │
│                                 │
│  1. Check if user has session   │──Yes──> Continue to page
│     (Auth::check())              │
│                                 │
│  2. No session?                 │
│     Check for token/signature   │
│                                 │
│  3. Validate credentials        │
│                                 │
│  4. Auth::login(user, true)     │──────> Set session cookie
│                                 │         (remember me)
│  5. Redirect to clean URL       │
└─────────────────────────────────┘
         │
         ▼
┌─────────────────┐
│  User navigates │
│  with session   │
│  (clean URLs)   │
└─────────────────┘
```

### Key Components

#### 1. AuthenticateTelegramUser Middleware
**Location**: [app/Http/Middleware/AuthenticateTelegramUser.php](../app/Http/Middleware/AuthenticateTelegramUser.php)

**Priority Order**:
1. If user has valid session → Continue
2. If `token` query parameter → Validate OneTimeToken, establish session, redirect to clean URL
3. If `u` parameter with valid signature → Decrypt telegram_id, establish session, redirect to clean URL
4. Otherwise → Return 401 Unauthorized

**Key Features**:
- Uses `Auth::login($user, true)` with "remember me" for persistent sessions
- Automatically removes token/signature parameters from URL after authentication
- Returns clean, bookmarkable URLs after first visit

#### 2. Route Protection
**Location**: [routes/web.php](../routes/web.php)

All authenticated routes wrapped in `telegram.auth` middleware:
```php
Route::middleware(['telegram.auth'])->group(function () {
    Route::get('/me', [DashboardController::class, 'show']);
    Route::get('/wager/{wager}', [WagerController::class, 'show']);
    // ... all other protected routes
});
```

#### 3. Simplified Controllers

**WagerController** and **DashboardController** simplified:
- Removed complex signed URL generation
- Removed dashboard token generation
- Now use simple `route('wager.show', ['wager' => $wager->id])` for links
- Session middleware handles all authentication

#### 4. Vue Component Cleanup

**Show.vue** improvements:
- Fixed memory leak: Added `onUnmounted()` to clean up `setInterval`
- Removed `dashboardToken` prop (no longer needed)
- Simplified back link to `/me` (session handles auth)

## User Flow Example

### First Visit from Telegram Bot

1. User clicks link from bot: `https://app.com/me?token=abc123...`
2. Middleware detects no session, validates token
3. Middleware calls `Auth::login($user, true)`
4. Laravel sets session cookie (encrypted, httpOnly, secure)
5. Middleware redirects to: `https://app.com/me` (clean URL)
6. User sees dashboard

### Subsequent Navigation

1. User clicks "View Wager" link: `https://app.com/wager/uuid`
2. Middleware checks session → Valid
3. User sees wager page immediately (no authentication needed)
4. User clicks "Back to Dashboard": `https://app.com/me`
5. Middleware checks session → Valid
6. User sees dashboard immediately

### Session Persistence

- **Default**: 120 minutes of inactivity
- **Remember Me**: Enabled by default (24 hours)
- **Storage**: Encrypted in database (`sessions` table)
- **Security**: httpOnly, secure, SameSite=Lax cookies

## Security Considerations

1. **First Entry**:
   - OneTimeToken: Single-use, 24-hour expiry
   - Signed URLs: Encrypted telegram_id, signature validation, 24-hour expiry

2. **Session Security**:
   - Encrypted session data
   - httpOnly cookies (not accessible via JavaScript)
   - Secure flag (HTTPS only in production)
   - SameSite=Lax (CSRF protection)

3. **Middleware Order**:
   - Session check first (performance)
   - Token validation second
   - Signature validation third
   - 401 response last

## Benefits

### Performance
- ✅ No memory leaks (fixed `setInterval` cleanup)
- ✅ No token generation overhead on every page
- ✅ Faster page loads (session check vs token validation)
- ✅ Reduced database queries

### UX
- ✅ Clean, bookmarkable URLs
- ✅ Fast navigation (no redirects after first visit)
- ✅ Standard browser back button works perfectly
- ✅ Session persists across browser tabs

### Maintainability
- ✅ Follows Laravel best practices
- ✅ Simpler controller code
- ✅ Standard authentication flow
- ✅ Easy to extend (logout, session management, etc.)

### Security
- ✅ Laravel's battle-tested session management
- ✅ Proper CSRF protection
- ✅ Secure cookie handling
- ✅ Automatic session rotation

## Migration Path

No database migrations needed - existing token system remains for initial authentication.

**Backward Compatibility**:
- Existing signed URLs still work
- Existing OneTimeTokens still work
- Telegram bot doesn't need changes
- ShortUrls continue to work

**New Behavior**:
- After first authentication, users have persistent session
- Navigation uses clean URLs
- Session persists until expiry or explicit logout

## Future Enhancements

1. **Logout Functionality**: Add `/logout` route to invalidate session
2. **Session Management**: Show active sessions in user profile
3. **Remember Me Toggle**: Let users choose session duration
4. **Activity Tracking**: Log last activity timestamp
5. **Multi-Device Support**: Allow multiple simultaneous sessions

## Testing Checklist

- [x] First visit with OneTimeToken establishes session
- [x] First visit with signed URL establishes session
- [x] Navigation after session uses clean URLs
- [x] Session persists across page refreshes
- [x] Memory leak fixed (browser doesn't freeze)
- [ ] Session expires after inactivity
- [ ] Remember me cookie works correctly
- [ ] Unauthorized access returns 401
- [ ] CSRF protection works on forms

## Files Changed

### Core Authentication
- `app/Http/Middleware/AuthenticateTelegramUser.php` (new)
- `bootstrap/app.php` (middleware registration)
- `routes/web.php` (route protection)

### Controllers
- `app/Http/Controllers/WagerController.php` (simplified)
- `app/Http/Controllers/DashboardController.php` (simplified)

### Frontend
- `resources/js/Pages/Wager/Show.vue` (memory leak fix + simplified auth)

### Documentation
- `claudedocs/session-auth-implementation.md` (this file)
