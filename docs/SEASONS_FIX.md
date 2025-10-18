# Seasons Authentication Fix

## Issue
When attempting to create a season, users encountered a server error:

```
Auth guard [sanctum] is not defined.
```

## Root Cause
The initial implementation placed season routes in `routes/api.php` with `auth:sanctum` middleware, but this project uses **web authentication** (session-based with `signed.auth` middleware) rather than API token authentication.

## Solution
Moved season routes from API to web authentication pattern:

### Changes Made

1. **Removed API Routes** (`routes/api.php`):
   - Removed all season routes from API routes file
   - Removed Sanctum middleware usage

2. **Added Web Routes** (`routes/web.php`):
   - Added season routes to web routes with `signed.auth` middleware
   - Changed route paths from `/api/groups/{group}/seasons` to `/groups/{group}/seasons`

3. **Created Web Controller** (`app/Http/Controllers/SeasonController.php`):
   - Moved from `Api` namespace to main `Controllers` namespace
   - Changed authentication from `$this->authorize()` to explicit user membership check:
   ```php
   $userGroup = $group->users()
       ->where('user_id', auth()->id())
       ->first();

   if (!$userGroup) {
       abort(403, 'You are not a member of this group');
   }
   ```
   - Matches pattern used in `GroupSettingsController`

4. **Updated Vue Components**:
   - `SeasonManagement.vue`: Changed axios URLs from `/api/groups/...` to `/groups/...`
   - `PastSeasons.vue`: Changed axios URLs from `/api/groups/...` to `/groups/...`

### Authentication Pattern

This project uses **one-time token authentication** for platform-agnostic access:

1. User receives signed URL from Telegram bot
2. First visit validates token and establishes web session
3. Subsequent requests use session (clean URLs, no tokens)
4. All routes use `signed.auth` middleware

### Routes After Fix

```php
// routes/web.php
Route::middleware(['signed.auth'])->group(function () {
    // ... other routes ...

    // Season management routes
    Route::get('/groups/{group}/seasons', [SeasonController::class, 'index'])->name('seasons.index');
    Route::get('/groups/{group}/seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
    Route::post('/groups/{group}/seasons', [SeasonController::class, 'store'])->name('seasons.store');
    Route::post('/groups/{group}/seasons/end', [SeasonController::class, 'end'])->name('seasons.end');
});
```

### Testing
After these changes:
1. Frontend build successful ✅
2. Routes registered correctly ✅
3. No Sanctum dependency ✅
4. Ready for user testing

## Deployment
When deploying this fix:

```bash
# Rebuild frontend assets
npm run build

# Clear route cache
php artisan route:clear

# Verify routes
php artisan route:list --path=seasons
```
