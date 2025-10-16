# Engagement Trigger System

**Status**: ✅ Complete (Phase 1)
**Last Updated**: October 16, 2025

## Overview

The engagement trigger system adds contextual personality and FOMO to wager join announcements by detecting specific conditions when users join wagers (e.g., "look who's back!", "the leader strikes!", "bold contrarian move!").

## Architecture

### 1. Event Flow

```
User clicks join button in Telegram
  ↓
TelegramWebhookController::handleCallbackQuery()
  ↓
WagerService::placeWager() → returns WagerEntry
  ↓
WagerJoined::dispatch($wager, $entry, $user)
  ↓
SendWagerJoinAnnouncement (queued listener)
  ↓
EngagementTriggerService::detectTriggers()
  ↓
MessageService::wagerJoined($wager, $entry, $user, $triggers)
  ↓
LLMService::generate($context) with trigger guidance
  ↓
Group::sendMessage() → Telegram announcement
```

### 2. Key Components

#### `/app/Events/WagerJoined.php`
Event dispatched when user successfully joins a wager.

```php
class WagerJoined
{
    public function __construct(
        public readonly Wager $wager,
        public readonly WagerEntry $entry,
        public readonly User $user
    ) {}
}
```

#### `/app/Listeners/SendWagerJoinAnnouncement.php`
Queued listener that detects triggers and sends LLM-powered announcement.

```php
class SendWagerJoinAnnouncement implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 5;

    public function handle(WagerJoined $event): void
    {
        // Detect engagement triggers
        $triggers = $this->triggerService->detectTriggers(
            $event->entry,
            $event->wager,
            $event->user,
            $event->wager->group
        );

        // Generate LLM message with triggers
        $message = $this->messageService->wagerJoined(
            $event->wager,
            $event->entry,
            $event->user,
            $triggers
        );

        // Send to group
        $event->wager->group->sendMessage($message);
    }
}
```

#### `/app/Services/EngagementTriggerService.php`
Detects all engagement triggers for a wager entry.

**Phase 1 Triggers (Implemented):**

**Position Triggers:**
- `is_first` - First person to join this wager (trendsetter!)
- `is_leader` - User is #1 on leaderboard (the leader makes their move!)
- `is_underdog` - User is in bottom 25% (underdog fighting back!)

**Stakes Triggers:**
- `is_high_stakes` - Wagering ≥50% of balance (high stakes/all-in energy!)
- `stake_percentage` - Exact percentage of balance wagered

**Comeback Triggers:**
- `is_comeback` - Recently had points decay (comeback after tough times!)
- `days_inactive` - Days since last wager join (e.g., "back after 14 days!")

**Momentum Triggers:**
- `is_contrarian` - Betting against majority (bold contrarian move!)
- `is_bandwagon` - Joining majority side (piling on with the crowd!)

**Timing Triggers:**
- `is_last_minute` - Joining within 6 hours of deadline (last-minute decision!)
- `is_early_bird` - Joined within 1 hour of creation (quick on the draw!)

**Context:**
- `leaderboard_rank` - Current rank in group leaderboard

#### `/app/Services/MessageService.php`
Generates messages by passing trigger data to LLM.

```php
public function wagerJoined(
    Wager $wager,
    WagerEntry $entry,
    User $user,
    array $engagementTriggers = []
): Message {
    $currency = $wager->group->points_currency_name ?? 'points';

    $ctx = new MessageContext(
        key: 'wager.joined',
        intent: 'Announce a user joining a wager to create FOMO and engagement (DO NOT reveal their answer - blind wagers!)',
        requiredFields: ['user_name', 'wager_title', 'points_wagered', 'currency'],
        data: [
            'user_name' => $user->name,
            'wager_title' => $wager->title,
            'points_wagered' => $entry->points_wagered,
            'currency' => $currency,
            'triggers' => $engagementTriggers, // Contextual triggers
            'total_pot' => $wager->total_points_wagered,
            'total_participants' => $wager->participants_count,
            // NOTE: answer_value intentionally excluded - blind wagers!
        ],
        group: $wager->group
    );

    $content = $this->contentGenerator->generate($ctx, $wager->group);
    return new Message(/*...*/);
}
```

#### `/app/Services/LLMService.php`
Enhanced with trigger guidance for personality.

**buildTriggersGuidance() Method:**
```php
private function buildTriggersGuidance(array $triggers): string
{
    $hints = [];

    if ($triggers['is_first'] ?? false) {
        $hints[] = "- First person to join this wager (trendsetter!)";
    }
    if ($triggers['is_leader'] ?? false) {
        $hints[] = "- This user is #1 on the leaderboard (the leader makes their move!)";
    }
    if ($triggers['is_comeback'] ?? false) {
        $days = $triggers['days_inactive'] ?? null;
        if ($days) {
            $hints[] = "- Back after {$days} days of inactivity (comeback story! welcome back!)";
        }
    }
    // ... more triggers

    return empty($hints) ? '' : implode("\n", $hints);
}
```

**LLM Prompt Enhancement:**
```
Generate a message with personality for this context:

Intent: Announce a user joining a wager to create FOMO and engagement
Tone hints: exciting, engaging, FOMO

Required fields: user_name, wager_title, answer, points_wagered, currency

Data:
{
  "user_name": "Xander",
  "wager_title": "Ajax - PSV",
  "answer": "yes",
  "points_wagered": 50,
  "currency": "chips",
  "triggers": {...}
}

Engagement Context (use for extra personality):
- First person to join this wager (trendsetter!)
- This user is #1 on the leaderboard (the leader makes their move!)
- Wagering 80% of their balance (high stakes/all-in energy!)

Rules:
- Include ALL required fields in natural language
- Keep under 25 words total
- Use 1-2 emojis
- Match the tone hints
- If engagement triggers are present, use them to add personality and create FOMO
```

## Example Outputs

### Scenario 1: Leader Goes All-In
**Triggers**: `is_leader: true`, `is_high_stakes: true`, `stake_percentage: 90`

**LLM Output**:
> 🔥 The LEADER just went ALL-IN! Xander wagered 450 chips on Ajax - PSV (yes). Who's brave enough to challenge? 👑

### Scenario 2: Comeback After Decay
**Triggers**: `is_comeback: true`, `days_inactive: 14`

**LLM Output**:
> 🎉 Look who's BACK! Xander returns after 14 days and bets 50 chips on Ajax - PSV (yes). Welcome back! 💪

### Scenario 3: Underdog Strikes
**Triggers**: `is_underdog: true`, `leaderboard_rank: 8`

**LLM Output**:
> 🐕 The underdog strikes! Xander (#8 on leaderboard) wagered 30 chips on Ajax - PSV (yes). Can they climb back? 📈

### Scenario 4: Contrarian Play
**Triggers**: `is_contrarian: true`

**LLM Output**:
> 💎 Bold contrarian move! Xander bucks the trend with 50 chips on Ajax - PSV (yes). Going against the grain! 🎲

### Scenario 5: Last-Minute Drama
**Triggers**: `is_last_minute: true`, `hours_to_deadline: 3`

**LLM Output**:
> ⏰ Last-minute drama! With just 3 hours left, Xander throws down 50 chips on Ajax - PSV (yes). Clutch timing! 🎯

### Scenario 6: No Triggers (Fallback)
**Triggers**: All false

**LLM Output**:
> ✅ Xander joined the wager "Ajax - PSV" with 50 chips. Total pot: 250 chips, 5 participants! 🎲

## Testing

### Manual Test via Telegram
1. Create a test wager in Telegram group
2. Have user join wager via inline button
3. Check group for announcement message
4. Verify triggers detected correctly in logs:
   ```
   grep "Engagement triggers detected" storage/logs/laravel.log
   ```

### Test Specific Triggers

**Test Leader Trigger:**
```bash
# Ensure user is #1 on leaderboard, then have them join wager
```

**Test Comeback Trigger:**
```sql
-- Simulate decay for user
INSERT INTO transactions (user_id, group_id, type, points, created_at)
VALUES ('{user_id}', '{group_id}', 'decay', -50, NOW() - INTERVAL '10 days');

-- Now have user join wager
```

**Test High Stakes:**
```sql
-- Set user balance to 100
UPDATE group_user SET points = 100 WHERE user_id = '{user_id}';

-- Create wager with 80 chip stake (80% threshold met)
```

### Unit Test Examples

```php
public function test_detects_leader_trigger()
{
    // Create user who is #1 on leaderboard
    $leader = User::factory()->create();
    $group = Group::factory()->create();
    $group->users()->attach($leader, ['points' => 1000]); // Highest

    $wager = Wager::factory()->create(['group_id' => $group->id]);
    $entry = WagerEntry::factory()->create([
        'wager_id' => $wager->id,
        'user_id' => $leader->id,
    ]);

    $triggers = app(EngagementTriggerService::class)
        ->detectTriggers($entry, $wager, $leader, $group);

    $this->assertTrue($triggers['is_leader']);
    $this->assertEquals(1, $triggers['leaderboard_rank']);
}

public function test_detects_comeback_trigger()
{
    $user = User::factory()->create();
    $group = Group::factory()->create();

    // Create decay transaction 10 days ago
    Transaction::factory()->create([
        'user_id' => $user->id,
        'group_id' => $group->id,
        'type' => 'decay',
        'points' => -50,
        'created_at' => now()->subDays(10),
    ]);

    $wager = Wager::factory()->create(['group_id' => $group->id]);
    $entry = WagerEntry::factory()->create([
        'wager_id' => $wager->id,
        'user_id' => $user->id,
    ]);

    $triggers = app(EngagementTriggerService::class)
        ->detectTriggers($entry, $wager, $user, $group);

    $this->assertTrue($triggers['is_comeback']);
}
```

## Future Enhancements (Phase 2-3)

See `/docs/ENGAGEMENT_TRIGGERS.md` for complete Phase 2-3 specification.

**Phase 2 Triggers (Nice-to-Have):**
- `revenge_bet` - Betting against someone who beat you in previous wager
- `nemesis_joins` - Your rival joins the same wager
- `first_bet_in_season` - First wager of new season

**Phase 3 Triggers (Future):**
- `winning_streak_vs_opponent` - 3+ wins against specific opponent
- `challenger_joins` - Lower-ranked user bets against higher-ranked on same wager

## Configuration

### Message Configuration
Edit `/lang/en/messages.php`:

```php
'joined' => [
    'intent' => 'Announce a user joining a wager to create FOMO and engagement',
    'required_fields' => ['user_name', 'wager_title', 'answer', 'points_wagered', 'currency'],
    'tone_hints' => ['exciting', 'engaging', 'FOMO'],
    'max_words' => 25, // Adjust for longer/shorter messages
],
```

### LLM Configuration
Groups configure via Settings > Bot Personality tab:
- API Key (encrypted)
- Provider (Anthropic, OpenAI, Requesty)
- Bot Tone (personality instructions)

### Trigger Thresholds
Edit `/app/Services/EngagementTriggerService.php`:

```php
// High stakes threshold
private function isHighStakes(WagerEntry $entry, User $user, Group $group): bool
{
    $balance = $this->getUserBalance($user, $group);
    $threshold = 0.50; // 50% of balance (adjust as needed)
    return $entry->points_wagered >= ($balance * $threshold);
}

// Last minute threshold
private function isLastMinute(Wager $wager): bool
{
    $hoursToDeadline = now()->diffInHours($wager->deadline, false);
    $threshold = 6; // 6 hours (adjust as needed)
    return $hoursToDeadline > 0 && $hoursToDeadline <= $threshold;
}
```

## Performance

- **SQL Queries**: 3-5 per trigger detection (cached leaderboard, efficient window functions)
- **Caching**: LLM responses cached for 1 hour with similar contexts
- **Queue**: All processing async via Laravel queues (3 retries, 5s backoff)
- **Impact**: <100ms added to wager join flow (async)

## Monitoring

### Operational Logs
```bash
grep "Engagement triggers detected" storage/logs/operational.log
```

### LLM Metrics
Check LLM Usage tab in group settings for:
- Total trigger-enhanced messages
- Cache hit rate
- Estimated cost per trigger

### Audit Events
```sql
SELECT * FROM audit_events
WHERE action = 'wager.joined'
ORDER BY created_at DESC
LIMIT 20;
```
