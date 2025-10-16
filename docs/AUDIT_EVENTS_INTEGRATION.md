# Audit Events Integration - Wager Settlement

## What Was Done

Integrated `AuditEventService` into `WagerService::settleWager()` to automatically create human-readable event summaries whenever wagers are settled.

## Changes Made

### 1. Updated WagerService.php

**Added import:**
```php
use App\Services\AuditEventService;
```

**Added audit event creation after settlement:**
```php
// Create audit events for LLM context
$this->createAuditEvents($wager);
```

**Added private method `createAuditEvents()`:**
- Handles 1v1 wagers with detailed winner/loser narrative
- Handles multi-participant wagers with summary
- Handles no-winner scenarios (refunds)

## Event Types Created

### 1. 1v1 Wagers (Most Common)

When exactly 2 participants:
```php
AuditEventService::wagerWon(
    group: $wager->group,
    winner: $winner->user,
    loser: $loser->user,
    points: $pointsGained,
    wagerTitle: $wager->title,
    wagerId: $wager->id
);
```

**Example event:**
```
Event Type: wager.won
Summary: "Sarah won 'Will John finish the marathon?' against John for 50 points"
Participants: 
  - Sarah (winner)
  - John (loser)
Impact: {points: 50}
```

### 2. Multi-Participant Wagers

When more than 2 participants:
```
Event Type: wager.multi_winner
Summary: "Alice, Bob won 'Best Pizza Place' and split 300 points"
Participants: [Alice (winner), Bob (winner)]
Impact: {total_points_won: 300}
```

### 3. No Winners (Refunds)

When no one wins:
```
Event Type: wager.no_winner
Summary: "'Impossible Bet' ended with no winners - all 5 participants were refunded"
Impact: {refund_count: 5}
```

## How It Works

1. **Wager is settled** → `WagerService::settleWager()` runs
2. **Winners/losers are determined** → Existing settlement logic
3. **Audit log created** → Existing `AuditService::log()` (compliance)
4. **Audit event created** → NEW: `createAuditEvents()` generates narrative
5. **Event stored in database** → `audit_events` table

## Database Records

Events are stored in `audit_events` table with:
- `group_id` - which group this happened in
- `event_type` - wager.won, wager.multi_winner, wager.no_winner
- `summary` - human-readable narrative
- `participants` - JSON array of users involved
- `impact` - JSON of what happened (points won, etc.)
- `metadata` - additional context (wager_id)
- `created_at` - when it happened

## Testing

### 1. Settle a 1v1 wager

```bash
# In Tinker
php artisan tinker

$wager = Wager::where('participants_count', 2)->first();
app(WagerService::class)->settleWager($wager, 'yes', 'Testing audit events');

# Check the event was created
$event = AuditEvent::latest()->first();
echo $event->summary;
// "Sarah won 'Test Wager' against John for 50 points"
```

### 2. Check database

```sql
SELECT 
    event_type,
    summary,
    participants,
    impact,
    created_at
FROM audit_events
ORDER BY created_at DESC
LIMIT 10;
```

### 3. Query recent events for a group

```php
$recentEvents = AuditEvent::recentForGroup($groupId, 10);
foreach ($recentEvents as $event) {
    echo $event->summary . "\n";
}
```

## Next Step: Use in LLM Context

Once events are being created, you can enhance LLM prompts with recent group history:

```php
// In LLMService::buildUserPrompt()
$recentEvents = AuditEvent::recentForGroup($ctx->group->id, 5);

if ($recentEvents->isNotEmpty()) {
    $summaries = $recentEvents->pluck('summary')->join("\n- ");
    $context = "\nRecent group activity:\n- {$summaries}\n";
}
```

This will make the LLM aware of context like:
- "Sarah beat John 3 times this week"
- "The group just had a 5-way tie"
- "Alice has been on a winning streak"

## Benefits

1. **Better LLM Context**: Bot can reference recent events naturally
2. **Group History**: Pre-aggregated narrative timeline
3. **Fast Queries**: Indexed and optimized for retrieval
4. **Human-Readable**: No parsing required, ready for display/LLM

## Monitoring

Check events are being created:

```bash
# Count events per group
psql your_db -c "SELECT group_id, COUNT(*) FROM audit_events GROUP BY group_id ORDER BY count DESC LIMIT 10;"

# Recent events
psql your_db -c "SELECT summary, created_at FROM audit_events ORDER BY created_at DESC LIMIT 20;"
```

## Future Enhancements

When ready, add more event types:
- Badge earnings → `AuditEventService::badgeEarned()`
- Grudge updates → `AuditEventService::grudgeUpdate()`
- Streak achievements → `AuditEventService::streakAchieved()`
- Streak breaks → `AuditEventService::streakBroken()`

All of these are already implemented in `AuditEventService` - just need to call them at the right places.
