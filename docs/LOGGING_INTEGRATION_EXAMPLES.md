# Logging Integration Examples

## Quick Reference

### Operational Logging (Monitoring)

```php
use App\Services\LogService;

// LLM generation
LogService::llm('generation.success', $group->id, [
    'message_key' => 'wager.announced',
    'provider' => 'anthropic',
    'duration_ms' => 245,
    'estimated_cost_usd' => 0.00013,
]);

// Performance tracking
LogService::performance('wager.settlement', 1234, [
    'wager_id' => $wager->id,
    'participants' => 5,
]);

// Feature usage
LogService::feature('revenge_bet', $group->id, $user->id);
```

### Audit Events (LLM Context)

```php
use App\Services\AuditEventService;

// Wager won
AuditEventService::wagerWon(
    group: $wager->group,
    winner: $winner,
    loser: $loser,
    points: $wager->stake,
    wagerTitle: $wager->title,
    wagerId: $wager->id
);

// Badge earned
AuditEventService::badgeEarned(
    group: $group,
    user: $user,
    badgeName: 'Hot Streak',
    description: '5 wins in a row',
    impact: ['streak_count' => 5]
);
```

---

## Integration Example: Wager Settlement

### In WagerService::settleWager()

After the settlement logic (around line 291):

```php
public function settleWager(
    Wager $wager,
    string $outcomeValue,
    ?string $settlementNote = null,
    ?string $settlerId = null
): Wager {
    $startTime = microtime(true);
    
    return DB::transaction(function () use ($wager, $outcomeValue, $settlementNote, $settlerId, $startTime) {
        // ... existing settlement logic ...
        
        // Audit log (existing)
        $settler = $settlerId ? User::find($settlerId) : null;
        AuditService::log(
            action: 'wager.settled',
            auditable: $wager,
            metadata: [
                'outcome_value' => $outcomeValue,
                'settlement_note' => $settlementNote,
                'winners_count' => $wager->entries()->where('is_winner', true)->count(),
                'total_pot' => $wager->total_points_wagered,
            ],
            actor: $settler
        );
        
        // NEW: Performance logging
        $duration = (int)((microtime(true) - $startTime) * 1000);
        LogService::performance('wager.settlement', $duration, [
            'wager_id' => $wager->id,
            'wager_type' => $wager->type,
            'participants' => $wager->entries_count,
            'total_pot' => $wager->total_points_wagered,
        ]);
        
        // NEW: Create audit events for LLM context
        // Only for binary/1v1 wagers (or adapt as needed)
        if ($wager->type === 'binary' && $wager->entries_count === 2) {
            $entries = $wager->entries;
            $winner = $entries->firstWhere('is_winner', true);
            $loser = $entries->firstWhere('is_winner', false);
            
            if ($winner && $loser) {
                AuditEventService::wagerWon(
                    group: $wager->group,
                    winner: $winner->user,
                    loser: $loser->user,
                    points: $winner->points_won - $winner->points_wagered,
                    wagerTitle: $wager->title,
                    wagerId: $wager->id
                );
            }
        }

        return $wager->fresh();
    });
}
```

---

## Integration Example: Badge System

When implementing badges, add audit events:

```php
class BadgeService
{
    public function checkAndAwardBadges(User $user, Group $group): array
    {
        $awarded = [];
        
        // Check for hot streak
        $streak = $this->calculateWinStreak($user, $group);
        if ($streak >= 5 && !$this->hasBadge($user, 'hot_streak')) {
            $this->awardBadge($user, 'hot_streak');
            
            // Create audit event
            AuditEventService::badgeEarned(
                group: $group,
                user: $user,
                badgeName: 'Hot Streak',
                description: '5 wins in a row',
                impact: ['streak_count' => $streak]
            );
            
            $awarded[] = 'hot_streak';
        }
        
        return $awarded;
    }
}
```

---

## Integration Example: Grudge/Rivalry Tracking

When detecting rivalries:

```php
class GrudgeService
{
    public function updateRivalries(Wager $wager): void
    {
        if ($wager->entries_count !== 2) {
            return; // Only 1v1 matters
        }
        
        $entries = $wager->entries;
        $winner = $entries->firstWhere('is_winner', true);
        $loser = $entries->firstWhere('is_winner', false);
        
        if (!$winner || !$loser) {
            return;
        }
        
        // Calculate head-to-head record
        $record = $this->getHeadToHeadRecord(
            $winner->user,
            $loser->user,
            $wager->group
        );
        
        // If rivalry threshold met (3+ matchups), create audit event
        $totalMatchups = $record['user1_wins'] + $record['user2_wins'];
        if ($totalMatchups >= 3) {
            $leader = $record['user1_wins'] > $record['user2_wins'] 
                ? $winner->user 
                : $loser->user;
            $underdog = $leader->id === $winner->user->id 
                ? $loser->user 
                : $winner->user;
            
            AuditEventService::grudgeUpdate(
                group: $wager->group,
                leader: $leader,
                underdog: $underdog,
                leaderWins: max($record['user1_wins'], $record['user2_wins']),
                underdogWins: min($record['user1_wins'], $record['user2_wins'])
            );
        }
    }
}
```

---

## Integration Example: Using Events in LLM Context

When building LLM prompts, include recent events:

```php
// In LLMService::buildUserPrompt()
private function buildUserPrompt(MessageContext $ctx): string
{
    $meta = __('messages.' . $ctx->key);
    $intent = $meta['intent'] ?? '';
    $toneHints = $meta['tone_hints'] ?? [];
    
    // Get recent group activity
    $recentEvents = AuditEvent::recentForGroup($ctx->group->id, 5);
    $context = '';
    
    if ($recentEvents->isNotEmpty()) {
        $summaries = $recentEvents->pluck('summary')->join("\n- ");
        $context = "\nRecent group activity:\n- {$summaries}\n";
    }
    
    $dataStr = json_encode($ctx->data, JSON_PRETTY_PRINT);

    return <<<PROMPT
Generate a message with personality for this context:

Intent: {$intent}
Tone hints: {$this->formatList($toneHints)}
{$context}
Required fields to include: {$this->formatList($ctx->requiredFields)}

Data:
{$dataStr}

Rules:
- Include ALL required fields in natural language
- Keep under 250 words
- Use appropriate emojis
- Match the tone hints
- Reference recent activity if relevant

Generate the message:
PROMPT;
}
```

---

## Monitoring Operational Logs

### Check LLM usage

```bash
# Recent LLM calls
tail -n 100 storage/logs/operational-$(date +%Y-%m-%d).log | jq -r 'select(.message | contains("llm.")) | .context'

# Cache hit rate today
grep "llm.generation" storage/logs/operational-$(date +%Y-%m-%d).log | jq '.context.cached' | grep -c true

# Average response time
grep "llm.generation.success" storage/logs/operational-$(date +%Y-%m-%d).log | jq '.context.duration_ms' | awk '{sum+=$1; count++} END {print sum/count "ms"}'

# Estimated daily cost
grep "llm.generation.success" storage/logs/operational-$(date +%Y-%m-%d).log | jq '.context.estimated_cost_usd' | awk '{sum+=$1} END {print "$" sum}'
```

### Check performance

```bash
# Slow operations
grep "performance" storage/logs/operational-*.log | jq 'select(.context.duration_ms > 1000) | .context' | less

# Average settlement time
grep "wager.settlement" storage/logs/operational-*.log | jq '.context.duration_ms' | awk '{sum+=$1; count++} END {print sum/count "ms"}'
```

---

## Next Steps

1. **Run migration**
   ```bash
   php artisan migrate
   ```

2. **Test operational logging**
   - Generate a message with LLM
   - Check `storage/logs/operational-YYYY-MM-DD.log`
   - Should see JSON entries

3. **Add audit events to settlement**
   - Edit `WagerService::settleWager()`
   - Add `AuditEventService::wagerWon()` call
   - Test settlement, check `audit_events` table

4. **Use events in LLM**
   - Modify `LLMService::buildUserPrompt()`
   - Add recent events to prompt
   - Test message generation

5. **Monitor and iterate**
   - Watch logs for patterns
   - Adjust retention as needed
   - Add more event types as features grow
