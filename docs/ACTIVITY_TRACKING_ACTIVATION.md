# Activity Tracking - Activation Guide

## ✅ Implementation Complete!

All infrastructure is in place and tested. The feature is currently **DISABLED** and ready for activation.

---

## What's Implemented

### 1. **Database** ✅
- `groups.last_activity_at` - Tracks last message timestamp
- `groups.inactivity_threshold_days` - Configurable per group (default: 14)
- Indexed for efficient queries

### 2. **Webhook Tracking** ✅
- `TelegramWebhookController::trackGroupActivity()` method
- Redis throttling (one DB write per group per day)
- Feature flag check (exits early if disabled)
- Operational logging

### 3. **Scheduled Job** ✅
- `CheckGroupActivity` command created
- Commented out in `routes/console.php`
- Dry-run mode for testing
- Feature flag check built-in

### 4. **Message Service** ✅
- `MessageService::revivalMessage()` method
- LLM integration with group personality
- Fallback templates if LLM unavailable

### 5. **Feature Flag** ✅
- `config/features.php` with comprehensive documentation
- `FEATURE_ACTIVITY_TRACKING` env variable (default: false)

---

## Activation Steps

### Step 1: Enable Feature Flag

```bash
# .env
FEATURE_ACTIVITY_TRACKING=true
```

### Step 2: Clear Config Cache

```bash
docker-compose exec app php artisan config:clear
```

### Step 3: Test Webhook Tracking

Send messages to your Telegram group and check logs:

```bash
docker-compose exec app tail -f storage/logs/operational-*.log | grep activity_tracking
```

**Expected output:**
```json
{
  "event": "activity_tracking.updated",
  "group_id": "abc123",
  "group_name": "Test Group",
  "date": "2025-10-18"
}
```

### Step 4: Test Revival Command (Dry Run)

```bash
docker-compose exec app php artisan activity:check --dry-run
```

**Expected output:**
```
Checking for inactive groups...
No inactive groups found.
```

Or if you have inactive groups:
```
Checking for inactive groups...
Found 1 inactive group(s)
Group: Test Group (inactive for 15 days)
  [DRY RUN] Would send revival message
```

### Step 5: Uncomment Scheduled Job

Edit `routes/console.php`:

```php
// BEFORE (commented out)
// Schedule::command('activity:check')
//     ->dailyAt('09:00')
//     ->withoutOverlapping()
//     ->onOneServer();

// AFTER (uncommented)
Schedule::command('activity:check')
    ->dailyAt('09:00')  // 9am - good time for engagement
    ->withoutOverlapping()
    ->onOneServer();
```

### Step 6: Verify Scheduler

```bash
docker-compose exec app php artisan schedule:list
```

Should show:
```
0 9 * * *  php artisan activity:check .......... Next Due: Tomorrow at 9:00 AM
```

---

## Testing Revival Messages

### Create Test Scenario

To test revival messages without waiting 14 days:

1. **Manually set last_activity_at in the past**

```bash
docker-compose exec app php artisan tinker
```

```php
$group = App\Models\Group::first();
$group->update([
    'last_activity_at' => now()->subDays(15),  // 15 days ago
    'inactivity_threshold_days' => 14
]);
```

2. **Run the command**

```bash
# Dry run first
docker-compose exec app php artisan activity:check --dry-run

# Then live run
docker-compose exec app php artisan activity:check
```

3. **Check your Telegram group**

You should receive a revival message with personality!

**Example messages based on bot tone:**

- **Sarcastic:** "Well well well, 15 days of silence. Did everyone forget how to wager? 👀"
- **Encouraging:** "Hey friends! It's been 15 days - time to get back in the game! Who's ready? 🎯"
- **Sports Commentator:** "And it's been 15 days since the last wager! Will this be a comeback for the ages? 🏆"

---

## Monitoring

### Operational Logs

```bash
# Track all activity tracking events
docker-compose exec app tail -f storage/logs/operational-*.log | grep activity_tracking

# Filter specific events
docker-compose exec app tail -f storage/logs/operational-*.log | grep "activity_tracking.revival_sent"
```

### Events Logged

1. **activity_tracking.updated** - Group activity was updated (once per day per group)
2. **activity_tracking.revival_sent** - Revival message sent successfully
3. **activity_tracking.revival_failed** - Error sending revival message

### Metrics to Watch

- **Daily activity updates:** Should match number of active groups
- **Revival messages sent:** Track engagement response rate
- **Cache hit rate:** Redis throttling effectiveness
- **DB query performance:** Should be fast with index

---

## Redis Optimization Details

### How It Works

```php
// First message of the day
trackGroupActivity($chatId)
  → Check Redis: "group_activity:{group_id}:{2025-10-18}"
  → Not found? Update DB + Cache key
  → Subsequent messages today: Cache hit, skip DB write

// Next day
trackGroupActivity($chatId)
  → Check Redis: "group_activity:{group_id}:{2025-10-19}"
  → Not found? Update DB + Cache key (new day)
```

### Performance Impact

**Without Redis throttling:**
- 100 groups × 50 messages/day = 5,000 DB writes/day

**With Redis throttling:**
- 100 groups × 1 update/day = 100 DB writes/day

**98% reduction in DB writes!** 🚀

---

## Customization

### Per-Group Thresholds

Groups can have different inactivity thresholds:

```php
// Sports betting group - high activity expected
$sportsGroup->update(['inactivity_threshold_days' => 7]);

// Casual friends - more lenient
$casualGroup->update(['inactivity_threshold_days' => 21]);
```

### Revival Message Timing

Change when revival messages are sent:

```php
// routes/console.php
Schedule::command('activity:check')
    ->dailyAt('18:00')  // 6pm instead of 9am
    ->withoutOverlapping()
    ->onOneServer();
```

### Custom Revival Messages

Groups with LLM configured get personalized messages based on their `bot_tone`:

```php
$group->update([
    'bot_tone' => 'Motivational coach who gets people hyped',
]);
```

Revival message will match that tone automatically!

---

## Troubleshooting

### No Activity Updates in Logs

**Cause:** Feature flag disabled or webhook not receiving messages

**Fix:**
```bash
# Check feature flag
docker-compose exec app php artisan tinker --execute="echo config('features.activity_tracking') ? 'ENABLED' : 'DISABLED';"

# Check webhook is receiving messages
docker-compose exec app tail -f storage/logs/laravel.log | grep "Message received"
```

### Revival Messages Not Sending

**Cause:** No groups meet inactivity criteria

**Fix:**
```bash
# Check which groups are inactive
docker-compose exec app php artisan tinker
```

```php
\App\Models\Group::where('is_active', true)
    ->whereNotNull('last_activity_at')
    ->whereRaw('last_activity_at < NOW() - INTERVAL \'1 day\' * inactivity_threshold_days')
    ->get(['id', 'name', 'last_activity_at', 'inactivity_threshold_days']);
```

### Cache Not Working

**Cause:** Redis not configured

**Fix:**
```bash
# Check Redis connection
docker-compose exec app php artisan tinker --execute="Cache::put('test', 'value'); echo Cache::get('test');"

# Should output: value

# If using file cache, it still works (just slower)
```

---

## Deactivation

If you need to disable the feature:

1. Set `FEATURE_ACTIVITY_TRACKING=false` in `.env`
2. Clear config: `php artisan config:clear`
3. Comment out scheduled job in `routes/console.php`

**Data is preserved** - you can re-enable anytime!

---

## Summary

**Status:** ✅ Ready for Production

**Activation:** One env variable + uncomment one line

**Performance:** Optimized with Redis throttling

**Testing:** Multiple verification steps included

**Monitoring:** Comprehensive operational logging

**Customization:** Per-group thresholds and LLM personalities

Let's bring those groups back to life! 🎯
