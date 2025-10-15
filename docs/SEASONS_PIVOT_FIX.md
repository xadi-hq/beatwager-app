# Seasons Pivot Table Fix

## Issue
When creating a season, the following SQL error occurred:

```
SQLSTATE[42703]: Undefined column: 7 ERROR: column "points" of relation "users" does not exist
LINE 1: update "users" set "points" = $1...
```

## Root Cause
The `SeasonService::createSeason()` method attempted to update the `users` table directly:

```php
$group->users()->update([
    'points' => $group->starting_balance,
    'points_earned' => 0,
    'points_spent' => 0,
]);
```

However, in this application, **points are stored in the `group_user` pivot table**, not on the `users` table. Each user has different point balances across different groups.

## Database Schema

### Pivot Table: `group_user`
```sql
- id (UUID)
- user_id (UUID)
- group_id (UUID)
- points (integer, default 1000)         ← Points are here!
- points_earned (integer, default 0)
- points_spent (integer, default 0)
- last_wager_joined_at (timestamp, nullable)
- last_activity_at (timestamp, nullable)
- role (enum: participant|admin)
- created_at, updated_at
```

### Users Table
The `users` table does NOT have `points`, `points_earned`, or `points_spent` columns. These are group-specific and stored in the pivot.

## Solution

### Fix 1: Update Pivot Table Directly
Changed from Eloquent relationship update to direct DB query:

```php
// BEFORE (incorrect)
$group->users()->update([
    'points' => $group->starting_balance,
    'points_earned' => 0,
    'points_spent' => 0,
]);

// AFTER (correct)
DB::table('group_user')
    ->where('group_id', $group->id)
    ->update([
        'points' => $group->starting_balance,
        'points_earned' => 0,
        'points_spent' => 0,
        'updated_at' => now(),
    ]);
```

### Fix 2: Order By Pivot Column
Changed leaderboard query to explicitly reference the pivot table column:

```php
// BEFORE (incorrect)
$users = $group->users()
    ->orderBy('points', 'desc')
    ->get();

// AFTER (correct)
$users = $group->users()
    ->orderBy('group_user.points', 'desc')
    ->get();
```

## Files Changed

- [app/Services/SeasonService.php](../app/Services/SeasonService.php)
  - Line 48-56: Fixed point reset to use pivot table
  - Line 131: Fixed leaderboard ordering to use pivot column

## Why This Works

1. **Direct Table Update**: Using `DB::table('group_user')` directly updates the pivot table without going through the Eloquent relationship, which was trying to update the users table.

2. **Qualified Column Name**: Using `group_user.points` explicitly tells the query which table's column to use for ordering.

## Testing

After this fix, creating a season should:
1. ✅ Reset all member points to the group's starting balance
2. ✅ Reset points_earned and points_spent to 0
3. ✅ Generate proper leaderboard when season ends
4. ✅ No SQL errors about missing columns

## Related Documentation

- [SEASONS_IMPLEMENTATION.md](SEASONS_IMPLEMENTATION.md) - Complete seasons feature documentation
- [SEASONS_FIX.md](SEASONS_FIX.md) - Authentication fix documentation
