# Activity Tracking Implementation Guide

## Overview

Activity tracking monitors group-level messaging activity and sends revival messages to inactive groups.

**Status:** Infrastructure prepared, feature DISABLED pending Telegram investigation

---

## Architecture

### Database Schema

```sql
-- groups table additions
last_activity_at TIMESTAMP NULL  -- Last message timestamp
inactivity_threshold_days INTEGER DEFAULT 14  -- Configurable per group
INDEX idx_groups_last_activity (last_activity_at)  -- For efficient queries
```

### Feature Flag

```php
// config/features.php
'activity_tracking' => env('FEATURE_ACTIVITY_TRACKING', false)
```

**Default:** `false` (disabled)

---

## Redis Optimization Strategy

### Problem

Updating `last_activity_at` on EVERY message could cause heavy DB load:
- High-volume groups: 100+ messages/day
- 100 groups × 100 messages = 10,000 DB writes/day

### Solution: Redis-Based Daily Throttling

```php
// In TelegramWebhookController or message handler

public function handleIncomingMessage($message)
{
    // ... existing message processing ...

    if (config('features.activity_tracking')) {
        $this->trackGroupActivity($group);
    }
}

private function trackGroupActivity(Group $group): void
{
    $today = now()->toDateString();
    $cacheKey = "group_activity:{$group->id}:{$today}";

    // Check if we've already updated today
    if (Cache::has($cacheKey)) {
        return; // Already tracked today, skip DB write
    }

    // First message of the day for this group
    $group->update(['last_activity_at' => now()]);

    // Cache for 24 hours (don't update again today)
    Cache::put($cacheKey, true, now()->endOfDay());
}
```

### Benefits

- **Reduces DB writes:** 10,000/day → ~100/day (one per group per day)
- **Maintains accuracy:** Tracks daily activity (sufficient for 14-day threshold)
- **Lightweight:** Redis check is fast (<1ms)
- **Self-cleaning:** Cache expires automatically at midnight

---

## Telegram Bot Configuration

### Requirements

**CRITICAL:** Telegram bot must have access to ALL messages, not just commands/mentions.

### Investigation Steps

1. **Check Bot Privacy Mode**
   ```
   /mybots → @YourBot → Bot Settings → Group Privacy
   ```
   - **Disabled** = Bot sees all messages ✅
   - **Enabled** = Bot only sees commands/mentions ❌

2. **Test Message Reception**
   ```php
   // In TelegramWebhookController, log all updates
   Log::info('Telegram update received', ['update' => $update]);
   ```

   Send normal messages (not commands) and check logs.

3. **Verify Scope**
   ```
   GET https://api.telegram.org/bot<TOKEN>/getMe
   ```

   Check `can_read_all_group_messages` field.

### If Telegram Doesn't Allow

**Fallback Options:**

1. **Track command usage only**
   - Less accurate but requires no special permissions
   - Update on `/newwager`, `/mybets`, etc.

2. **Disable feature entirely**
   - Keep schema for future use
   - Document limitation in README

---

## Activation Checklist

When ready to enable:

- [ ] Confirm Telegram bot has Group Privacy DISABLED
- [ ] Test message reception in dev/staging
- [ ] Verify Redis is available and configured
- [ ] Set `FEATURE_ACTIVITY_TRACKING=true` in `.env`
- [ ] Uncomment scheduled job in `app/Console/Kernel.php`
- [ ] Unhide UI components (remove feature flag checks)
- [ ] Test with small group first
- [ ] Monitor operational logs for errors
- [ ] Check DB query performance

---

## Scheduled Job

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule): void
{
    // DISABLED - Uncomment when activity tracking is activated
    // $schedule->command('activity:check')->daily();
}
```

### Manual Testing

```bash
# Dry run (shows what would happen without sending)
php artisan activity:check --dry-run

# Live run (sends revival messages)
php artisan activity:check
```

---

## Message Generation

Revival messages use the LLM service with group personality:

```php
// lang/en/messages.php
'activity' => [
    'revival' => [
        'intent' => 'Re-engage an inactive group with humor and encourage activity',
        'required_fields' => ['days_inactive'],
        'fallback_template' => "😴 Haven't heard from you in {days_inactive} days!\n\nTime to wake up and place some wagers! Who's in?",
        'tone_hints' => ['playful', 'encouraging', 'energetic'],
        'max_words' => 40,
    ],
]
```

**LLM Examples:**

- **Sarcastic tone:** "Well well well, 14 days of silence. Did everyone forget how to wager? 👀"
- **Encouraging tone:** "Hey friends! It's been 14 days - time to get back in the game! Who's ready? 🎯"
- **Sports commentator:** "And it's been 14 days since the last wager! Will this be a comeback for the ages? 🏆"

---

## Monitoring

### Operational Logs

```bash
tail -f storage/logs/operational-*.log | grep activity_tracking
```

**Events logged:**
- `activity_tracking.revival_sent` - Revival message successfully sent
- `activity_tracking.revival_failed` - Error sending revival message

### Metrics to Monitor

- Number of inactive groups detected daily
- Revival message success rate
- Group re-engagement rate (did they respond?)
- DB query performance for inactive group lookup

---

## Performance Considerations

### Query Optimization

The scheduled job uses an indexed query:

```sql
SELECT * FROM groups
WHERE is_active = true
  AND last_activity_at IS NOT NULL
  AND last_activity_at < NOW() - INTERVAL '1 day' * inactivity_threshold_days;
```

**Index used:** `idx_groups_last_activity`

### Scaling

- **10 groups:** Negligible impact
- **100 groups:** ~100 DB writes/day (with Redis throttling)
- **1,000 groups:** ~1,000 DB writes/day (still manageable)
- **10,000+ groups:** Consider batch updates or queue workers

---

## Future Enhancements

### Phase 2 (After Initial Validation)

1. **Smart Revival Timing**
   - Send during group's most active hours
   - Avoid late night/early morning

2. **Graduated Escalation**
   - Day 14: Gentle reminder
   - Day 21: More urgent message
   - Day 30: "We miss you!" with suggested wagers

3. **Re-engagement Tracking**
   - Did group respond to revival?
   - Success rate per personality type
   - Optimize message timing based on response patterns

4. **Custom Revival Triggers**
   - Group admins set custom thresholds
   - Different messages for different inactivity periods

---

## Rollback Plan

If issues arise after activation:

1. **Immediate:** Set `FEATURE_ACTIVITY_TRACKING=false`
2. **Stop job:** Comment out schedule in Kernel.php
3. **Flush cache:** `php artisan cache:clear`
4. **Investigate:** Check logs for errors
5. **Fix and retest:** Use `--dry-run` flag

**Data is preserved** - schema stays in place for future attempts.

---

## Summary

**Prepared:**
- ✅ Database schema with indexed fields
- ✅ Feature flag system
- ✅ Scheduled job (disabled)
- ✅ Message service integration
- ✅ LLM message templates
- ✅ Redis optimization strategy documented

**Pending:**
- ⏳ Telegram bot permission investigation
- ⏳ Feature flag activation
- ⏳ Scheduled job uncommented
- ⏳ UI components revealed

**Activation:** One env variable flip when ready! 🚀
