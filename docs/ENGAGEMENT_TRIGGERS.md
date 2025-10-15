# Wager Join Engagement Triggers

## Overview
When a user joins a wager, we detect contextual triggers to make the LLM announcement more engaging and create FOMO.

## Trigger Categories

### 1. **Return Triggers** (Grudge Memory)
Detect if user has been inactive and returned

**`comeback_after_decay`**
- **SQL**: Check if user has decay transactions in last 14-30 days
- **Context**: "Back after {days} days!"
- **Tone**: Celebratory, welcoming back
- **Example**: "Look who's made it back! Sarah returns after 3 weeks to join Ajax - PSV!"

**`first_bet_in_season`**
- **SQL**: Check if this is user's first wager entry in current season
- **Context**: "First bet of the season"
- **Tone**: Fresh start, excitement
- **Example**: "Sarah kicks off their season with Ajax - PSV! Let's see if they've still got it 🔥"

**`back_from_long_break`**
- **SQL**: Last wager entry >30 days ago (but no decay yet)
- **Context**: "Back from a break"
- **Tone**: Welcoming
- **Example**: "Sarah resurfaces after a month! Jumping into Ajax - PSV with confidence 💪"

### 2. **Status Triggers** (Leaderboard Position)

**`leader_joins`**
- **SQL**: User is #1 in group leaderboard
- **Context**: "The leader makes their move"
- **Tone**: Intimidating, pressure
- **Example**: "The king has spoken! Leader Sarah locks in Ajax - PSV. Can anyone stop them?"

**`underdog_joins`**
- **SQL**: User is in bottom 25% of active participants
- **Context**: "The underdog strikes"
- **Tone**: Hopeful, rooting for them
- **Example**: "Sarah's climbing back! Bottom-ranked but swinging for Ajax - PSV 🎯"

**`challenger_joins`**
- **SQL**: User is #2 or #3, leader already joined opposite side
- **Context**: "Direct challenge to leader"
- **Tone**: Dramatic, rivalry
- **Example**: "Sarah challenges the leader! This Ajax - PSV bet is getting spicy 🌶️"

### 3. **Momentum Triggers**

**`first_to_join`**
- **SQL**: This is the first entry after wager creation
- **Context**: "Breaking the ice"
- **Tone**: Bold, trendsetter
- **Example**: "Sarah breaks the ice on Ajax - PSV! Who's next? 👀"

**`bandwagon_effect`**
- **SQL**: >60% of participants already on same side
- **Context**: "Joining the majority"
- **Tone**: Safety in numbers vs. contrarian opportunity
- **Example**: "Sarah piles on with the majority! Ajax is the clear favorite now 📊"

**`contrarian_bet`**
- **SQL**: Joining the minority side when >60% on other side
- **Context**: "Going against the grain"
- **Tone**: Bold, risky, confident
- **Example**: "Bold move! Sarah bets against the crowd on PSV. Confident or crazy? 🤔"

**`high_stakes_entry`**
- **SQL**: Wagering >50% of current balance
- **Context**: "All-in energy"
- **Tone**: Dramatic, risky
- **Example**: "Sarah goes BIG on Ajax - PSV! 200 chips on the line! 💰"

### 4. **Grudge/Rivalry Triggers**

**`revenge_bet`**
- **SQL**: Last direct wager vs opponent, user lost
- **Context**: "Revenge match"
- **Tone**: Dramatic, grudge match
- **Example**: "Revenge time! Sarah lost to John last time. Now they're facing off again on Ajax - PSV!"

**`winning_streak_vs_opponent`**
- **SQL**: Won last 3+ wagers against specific opponent
- **Context**: "Dominating rivalry"
- **Tone**: Intimidating, confident
- **Example**: "Sarah's on fire! 4 wins in a row vs John. Can they keep the streak alive?"

**`nemesis_joins`**
- **SQL**: Most frequent opponent in wager history
- **Context**: "Classic rivalry"
- **Tone**: Dramatic, anticipation
- **Example**: "The nemesis appears! Sarah vs John on Ajax - PSV - their 10th battle!"

### 5. **Timing Triggers**

**`last_minute_entry`**
- **SQL**: Joining within 24 hours of deadline
- **Context**: "Procrastinator / Late move"
- **Tone**: Suspenseful, urgent
- **Example**: "Sarah slides in at the last second! Only 6 hours left on Ajax - PSV ⏰"

**`early_bird`**
- **SQL**: Joining within 1 hour of wager creation
- **Context**: "Quick on the draw"
- **Tone**: Eager, confident
- **Example**: "Sarah's lightning fast! Joined Ajax - PSV within minutes of it going live ⚡"

## SQL Query Patterns

### Comeback Detection
```sql
-- Check for decay in last 30 days
SELECT EXISTS(
    SELECT 1 FROM transactions
    WHERE user_id = ? AND group_id = ?
    AND type = 'decay'
    AND created_at >= NOW() - INTERVAL '30 days'
)

-- Check last wager entry date
SELECT MAX(created_at) FROM wager_entries
WHERE user_id = ? AND wager_id IN (
    SELECT id FROM wagers WHERE group_id = ?
)
```

### Leaderboard Position
```sql
-- Get user rank in group
SELECT rank FROM (
    SELECT user_id,
           RANK() OVER (ORDER BY points DESC) as rank
    FROM group_user
    WHERE group_id = ?
) ranks
WHERE user_id = ?

-- Total active participants
SELECT COUNT(*) FROM group_user
WHERE group_id = ? AND points > 0
```

### Entry Statistics
```sql
-- First entry check
SELECT COUNT(*) FROM wager_entries
WHERE wager_id = ?

-- Side distribution
SELECT answer_value, COUNT(*) as count
FROM wager_entries
WHERE wager_id = ?
GROUP BY answer_value

-- User's wagered amount vs balance
SELECT we.points_wagered, gu.points as balance
FROM wager_entries we
JOIN group_user gu ON we.user_id = gu.user_id AND gu.group_id = ?
WHERE we.id = ?
```

### Rivalry Detection
```sql
-- Get head-to-head record
SELECT
    SUM(CASE WHEN we1.is_winner = true THEN 1 ELSE 0 END) as wins,
    SUM(CASE WHEN we2.is_winner = true THEN 1 ELSE 0 END) as losses,
    COUNT(*) as total_matchups
FROM wager_entries we1
JOIN wager_entries we2 ON we1.wager_id = we2.wager_id
JOIN wagers w ON we1.wager_id = w.id
WHERE we1.user_id = ?
  AND we2.user_id = ?
  AND w.group_id = ?
  AND w.status = 'settled'
  AND we1.answer_value != we2.answer_value

-- Most frequent opponent
SELECT opponent_id, COUNT(*) as battles
FROM (
    SELECT we2.user_id as opponent_id
    FROM wager_entries we1
    JOIN wager_entries we2 ON we1.wager_id = we2.wager_id
    JOIN wagers w ON we1.wager_id = w.id
    WHERE we1.user_id = ?
      AND we2.user_id != ?
      AND w.group_id = ?
      AND we1.answer_value != we2.answer_value
) rivalries
GROUP BY opponent_id
ORDER BY battles DESC
LIMIT 1
```

## Implementation Priority

### Phase 1 (Must Have)
1. ✅ `first_to_join` - Simple count check
2. ✅ `leader_joins` - Leaderboard position
3. ✅ `high_stakes_entry` - Compare wager vs balance
4. ✅ `comeback_after_decay` - Check decay transactions
5. ✅ `contrarian_bet` - Side distribution check

### Phase 2 (Nice to Have)
6. `revenge_bet` - Head-to-head history
7. `nemesis_joins` - Most frequent opponent
8. `bandwagon_effect` - Majority side detection
9. `last_minute_entry` - Time to deadline check
10. `early_bird` - Time since creation check

### Phase 3 (Future)
11. `winning_streak_vs_opponent` - Complex rivalry tracking
12. `challenger_joins` - Leader opposition detection
13. `back_from_long_break` - Inactivity without decay
14. `first_bet_in_season` - Season context

## LLM Integration

Each trigger provides context data to LLM:

```php
$ctx = new MessageContext(
    key: 'wager.joined',
    intent: $meta['intent'],
    requiredFields: $meta['required_fields'],
    data: [
        'user_name' => $user->name,
        'wager_title' => $wager->title,
        'answer' => $entry->answer_value,
        'points_wagered' => $entry->points_wagered,
        'currency' => $currency,

        // Engagement triggers (optional)
        'triggers' => [
            'is_leader' => true,
            'is_first' => false,
            'is_high_stakes' => true,
            'is_comeback' => false,
            'is_contrarian' => true,
            'days_inactive' => null,
            'nemesis_name' => null,
            // ... more triggers
        ],

        // Additional context for LLM
        'total_pot' => $wager->total_points_wagered,
        'total_participants' => $wager->participants_count,
    ],
    group: $group
);
```

LLM prompt will instruct:
- Use triggers to add personality
- Create FOMO if multiple interesting triggers
- Keep to 20-30 words
- Include 1-2 emojis
- Make it engaging for others to join

## Example Outputs

**Simple join:**
> "Sarah joined Ajax - PSV for 50 chips! 💰"

**With triggers (leader + high stakes):**
> "The leader strikes! Sarah bets BIG on Ajax - PSV with 200 chips! Who dares challenge them? 👑"

**With triggers (comeback + contrarian):**
> "Look who's back! Sarah returns from the shadows and boldly bets against the crowd on PSV! 🔥"

**With triggers (nemesis + revenge):**
> "Grudge match alert! Sarah faces nemesis John again on Ajax - PSV. Revenge is on the menu! ⚔️"
