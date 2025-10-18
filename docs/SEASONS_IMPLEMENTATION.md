# Seasons Feature Implementation

## Overview
The Seasons feature allows groups to organize competition into time-bound periods with historical tracking, statistics, and LLM-powered recaps.

## Database Schema

### `group_seasons` Table
- `id` (UUID) - Primary key
- `group_id` (UUID) - Foreign key to groups table
- `season_number` (integer) - Sequential season number (1, 2, 3, etc.)
- `started_at` (timestamp) - When the season began
- `ended_at` (timestamp, nullable) - When the season ended
- `is_active` (boolean) - Whether this is the active season
- `final_leaderboard` (JSON, nullable) - Final rankings when season ends
- `stats` (JSON, nullable) - Season statistics (total wagers, participants, duration)
- `highlights` (JSON, nullable) - Season highlights (biggest win, most active, etc.)

**Indexes:**
- `(group_id, is_active)` - Fast lookup of active seasons
- `(group_id, season_number)` - Fast lookup by season number
- Unique constraint on `(group_id, season_number)` - Prevent duplicates

### `groups` Table Additions
- `current_season_id` (UUID, nullable) - Foreign key to current active season
- `season_ends_at` (timestamp, nullable) - Optional automatic end date

## Backend Components

### Models

#### `app/Models/GroupSeason.php`
- Relationships: `group()`, `wagers()`
- Helper methods:
  - `isActive()` - Check if season is active
  - `hasEnded()` - Check if season has ended
  - `getDurationInDays()` - Calculate duration
  - `getWinner()` - Get top-ranked player
  - `getTopPlayers(int $limit)` - Get top N players

#### `app/Models/Group.php` Updates
- Relationships: `seasons()`, `currentSeason()`
- Fillable: `current_season_id`, `season_ends_at`
- Casts: `season_ends_at` as datetime

### Services

#### `app/Services/SeasonService.php`
Handles all season lifecycle operations:

- **`createSeason(Group $group, ?Carbon $endsAt = null)`**
  - Deactivates old season
  - Creates new season with incremented season_number
  - Resets all member points to starting balance
  - Updates group's current_season_id

- **`endSeason(Group $group)`**
  - Marks season as inactive
  - Calculates final leaderboard
  - Generates season statistics
  - Generates season highlights
  - Clears group's current_season_id

- **`calculateFinalLeaderboard(GroupSeason $season)`**
  - Ranks all participants by points
  - Returns array of players with rank, name, points

- **`calculateSeasonStats(GroupSeason $season)`**
  - Total wagers count
  - Total participants count
  - Duration in days

- **`generateHighlights(GroupSeason $season)`**
  - Biggest win (wager with highest payout)
  - Most active creator (user who created most wagers)
  - Most participated wager (wager with most participants)

### Controllers

#### `app/Http/Controllers/Api/SeasonController.php`
RESTful API endpoints:

- `GET /api/groups/{group}/seasons` - List all seasons
- `GET /api/groups/{group}/seasons/{season}` - Get season details
- `POST /api/groups/{group}/seasons` - Create new season
- `POST /api/groups/{group}/seasons/end` - End current season

All endpoints use `auth:sanctum` middleware and policy authorization.

#### `app/Http/Controllers/GroupController.php` Updates
- Loads current season data
- Loads past 10 seasons
- Passes data to Inertia view

### Commands

#### `app/Console/Commands/CheckSeasonEndings.php`
- Signature: `seasons:check {--dry-run}`
- Scheduled: Daily at 00:01
- Finds groups with `season_ends_at <= now()`
- Ends seasons automatically
- Sends LLM-powered recap message

### Messages

#### `lang/en/messages.php` - Season Ended Message
```php
'season' => [
    'ended' => [
        'intent' => 'Announce season ending with dramatic recap and celebrate the winner',
        'required_fields' => ['season_number', 'winner_name', 'winner_points', 'duration_days'],
        'fallback_template' => '🏆 Season {season_number} Has Ended!...',
        'tone_hints' => ['dramatic', 'celebratory', 'nostalgic'],
        'max_words' => 200,
    ],
],
```

#### `app/Services/MessageService.php::seasonEnded()`
Generates LLM-powered season recap with:
- Season number and duration
- Winner celebration
- Top 3 leaderboard
- Season highlights (biggest win, most active, most participated)
- Statistics (total wagers, participants)

## Frontend Components

### Vue Components

#### `resources/js/Components/SeasonManagement.vue`
Season controls:
- Display current season info (number, days elapsed, end date)
- "Start New Season" button (with optional end date)
- "End Season Now" button (for active seasons)
- Info box explaining how seasons work

Features:
- Confirmation dialogs before destructive actions
- Loading states
- Optional end date selection (date picker)
- Visual indication of days remaining

#### `resources/js/Components/PastSeasons.vue`
Historical season browser:
- List of past seasons (most recent first, limit 10)
- Expandable details per season
- Season summary: number, dates, duration, winner, stats
- Detailed view:
  - Final leaderboard (top 5 with medals)
  - Season highlights (biggest win, most active, most participated)
  - Full statistics

Features:
- Click to expand/collapse details
- Lazy-loading of detailed data via API
- Formatted dates and durations
- Color-coded highlights

### Page Updates

#### `resources/js/Pages/Groups/Show.vue`
- Added TypeScript interfaces for season data
- New "🏆 Seasons" button (shows active season badge)
- Seasons drawer with SeasonManagement and PastSeasons components
- Props include `current_season`, `season_ends_at`, `pastSeasons`

## API Routes

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/groups/{group}/seasons', [SeasonController::class, 'index']);
    Route::get('/groups/{group}/seasons/{season}', [SeasonController::class, 'show']);
    Route::post('/groups/{group}/seasons', [SeasonController::class, 'store']);
    Route::post('/groups/{group}/seasons/end', [SeasonController::class, 'end']);
});
```

## Scheduled Jobs

```php
// routes/console.php
Schedule::command('seasons:check')
    ->dailyAt('00:01')
    ->withoutOverlapping()
    ->onOneServer();
```

## User Flow

### Starting a Season

1. User clicks "🏆 Seasons" button
2. Opens Seasons drawer
3. Clicks "🚀 Start New Season"
4. Optionally sets end date
5. Confirms action
6. Backend:
   - Deactivates old season (if exists)
   - Creates new season
   - Resets all member points
7. Page reloads with new season active

### Ending a Season (Manual)

1. User clicks "End Season Now"
2. Confirms action
3. Backend:
   - Calculates final leaderboard
   - Generates statistics
   - Generates highlights
   - Marks season inactive
   - Sends LLM recap to group
4. Page reloads showing season history

### Ending a Season (Automatic)

1. Scheduled job runs daily at 00:01
2. Finds groups with `season_ends_at <= now()`
3. Ends each season (same as manual)
4. Sends LLM recap to group

### Viewing Past Seasons

1. User clicks "🏆 Seasons" button
2. Scrolls to "📜 Past Seasons" section
3. Clicks on a season to expand
4. Views:
   - Final leaderboard (top 5)
   - Season highlights
   - Full statistics

## Data Flow

### Season Creation
```
User → Vue Component → API POST /api/groups/{id}/seasons
                     → SeasonService::createSeason()
                     → DB: Create season, update group, reset points
                     → Response
                     → Reload page
```

### Season Ending
```
Scheduled Job → CheckSeasonEndings command
             → Find groups with season_ends_at <= now()
             → SeasonService::endSeason()
             → Calculate leaderboard, stats, highlights
             → MessageService::seasonEnded()
             → LLM generates recap
             → Send to Telegram
             → Update DB
```

### Viewing Season Details
```
User → Click season → API GET /api/groups/{id}/seasons/{season_id}
                   → Load season with relationships
                   → Return JSON with leaderboard, stats, highlights
                   → Display in expandable UI
```

## Design Decisions

### Optional End Dates
- Seasons can run indefinitely until manually ended
- Scheduled job only processes seasons with explicit end dates
- Gives groups flexibility in competition structure

### JSON Storage for Flexibility
- `final_leaderboard`, `stats`, `highlights` stored as JSON
- Allows schema evolution without migrations
- Easy to add new highlight types
- Simple to query past seasons

### Point Reset on Season Start
- All members reset to `starting_balance`
- Fresh competition each season
- Previous season standings preserved in history

### Top 10 Past Seasons
- Limits UI clutter
- Most relevant historical data
- Can be adjusted if needed

### LLM Integration
- Generates dramatic, engaging season recaps
- Celebrates winners with personality
- Includes highlights and statistics
- Falls back to template if LLM unavailable

## Testing

### Manual Testing Checklist

1. **Start Season (No End Date)**
   - Click "Start New Season"
   - Don't set end date
   - Confirm all points reset
   - Verify season shows as "No end date set"

2. **Start Season (With End Date)**
   - Click "Start New Season"
   - Set end date to tomorrow
   - Confirm all points reset
   - Verify "X days left" displays

3. **End Season Manually**
   - Click "End Season Now"
   - Verify confirmation dialog
   - Check season archived in history
   - Verify LLM message sent to group

4. **View Past Seasons**
   - Click on past season
   - Verify details load
   - Check leaderboard displays correctly
   - Verify highlights show

5. **Automatic Ending (Dry Run)**
   ```bash
   php artisan seasons:check --dry-run
   ```
   - Set season end date to past
   - Verify command detects it
   - Confirm no actual changes

6. **Automatic Ending (Real)**
   ```bash
   php artisan seasons:check
   ```
   - Verify season ends
   - Check recap message sent

### Edge Cases

- No members in group (empty leaderboard)
- No wagers in season (no highlights)
- Multiple seasons in history
- Season with only 1 participant
- Ending season immediately after creation

## Future Enhancements

- Season templates (custom rules per season)
- Season-specific wager types or bonuses
- Season playoff/tournament mode
- Export season data as PDF/CSV
- Season comparison view
- Custom season duration presets (weekly, monthly, quarterly)
- Season achievements/badges
