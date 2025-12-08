# PRD: Elimination Challenge - Last Man Standing / Deadline Survival

**Created:** December 8, 2025
**Status:** Draft - Design Complete
**Priority:** Post-MVP Enhancement
**Estimated Effort:** 10-14 hours

---

## TL;DR

**What:** Survival-style challenges where participants "tap in" to join and "tap out" when eliminated. Survivors split a point pot.

**Why:** Creates social intrigue and sustained engagement over days/weeks. The split-pot mechanic incentivizes participants to subtly "help" others get eliminated.

**How:** Extends existing challenge system with new `ELIMINATION_CHALLENGE` type, buy-in commitment, and LLM-driven countdown messaging.

**Examples:**
- Wham! Challenge: Avoid hearing "Last Christmas" until Dec 24
- Dry January: No alcohol until month end
- No Nut November: Self-explanatory

---

## Problem Statement

### Current State
Existing challenges are either:
- **User Challenge (Marketplace):** 1-on-1 point exchanges, immediate resolution
- **Super Challenge:** Group-wide, everyone who completes wins, cooperative

### The Gap
- No **long-duration** challenges (days/weeks)
- No **elimination-style** competition (last man standing)
- No **social engineering** dynamics (incentive to eliminate others)
- No **passive participation** (survive by NOT doing something)

### Opportunity
Introduce **elimination challenges** that:
- Run over extended periods (days to weeks)
- Create water-cooler moments ("Did you hear Sarah got eliminated?")
- Incentivize playful sabotage (split pot = fewer survivors = more points)
- Generate sustained engagement through countdown messaging

---

## Solution Overview

### Core Concept

**Elimination Challenge:** A survival competition where:
1. Creator defines elimination trigger (e.g., "Hearing 'Last Christmas'")
2. Participants "tap in" with a buy-in commitment
3. When eliminated, participants "tap out" (honor system)
4. Challenge ends when: last person standing OR deadline reached
5. Survivors split the pot (system-matched to target size)

### Two Modes

| Mode | End Condition | Point Distribution | Social Dynamic |
|------|---------------|-------------------|----------------|
| **Last Man Standing** | 1 survivor remains | Winner takes full pot | Pure elimination |
| **Deadline** | Date reached | Pot split among survivors | Incentive to eliminate others | Default for when group is in an active season 

### Example Flow (Deadline Mode)

```
[Creator] → /newchallenge → Selects "Elimination challenge"
    ↓
[Form] → Name: "Wham! Challenge 2025"
         Trigger: "Hearing 'Last Christmas' by Wham!"
         Mode: Deadline (Dec 24, 2025)
         Pot: 1,050 pts (suggested based on group economy)
    ↓
[System] → Calculates buy-in: 52.5 pts per person
         → Posts challenge to group with "Tap In" button
    ↓
[8 users tap in] → 8 × 52.5 = 420 pts collected
                 → System adds 630 pts to reach 1,050 pot
    ↓
[Day 5] → @Emma taps out: "It was in the supermarket"
        → LLM announces: "💀 @Emma has fallen! 7 remain, 150pts each"
    ↓
[Day 12] → @John taps out
         → 50% eliminated milestone (if 3+ remain)
    ↓
[48 hours left] → LLM: "The stakes are rising. 4 survivors, 262pts each.
                       But what if there were only 2...? 🤔"
    ↓
[Dec 24] → 3 survivors remain
         → Each receives 350 pts (1,050 ÷ 3)
         → LLM announces victory + final standings
```

---

## Key Features

### 1. Challenge Creation

**Via `/newchallenge` command → Web form:**

Creator sets:
- **Name:** "Wham! Challenge 2025"
- **Elimination trigger:** "Hearing 'Last Christmas' by Wham!"
- **Mode:** `last_man_standing` | `deadline`
- **End date:** (required for deadline mode, defaults to season end if in season)
- **Tap-in deadline:** When registration closes (optional)

**System calculates:**
- **Suggested pot:** 10% of current total group currency
- **Buy-in:** `(pot / group_size) * 0.5`

**Creator can adjust:**
- Pot size (within reasonable bounds)
- Buy-in auto-recalculates

### 2. Pot & Buy-in Economics

**Formula:**
```
Total group currency = sum of all user balances + created points
Suggested pot = total * 10%
Buy-in per person = (pot / group_size) * 50%
```

**Example:**
```
10 users × 1,000 pts each + 500 bonus pts = 10,500 total
Suggested pot = 1,050 pts
Buy-in = (1,050 / 10) × 0.5 = 52.5 pts per person
```

**Funding mechanism:**
- Participants pay buy-in on tap-in
- System "matches" to reach full pot
- If 6 of 10 tap in: 315 collected + 735 system = 1,050 pot

**This creates points** (inflationary, like Super Challenge) but with skin-in-the-game commitment.

### 3. Participation Flow

**Tap In:**
- Click button in group announcement
- Buy-in deducted immediately
- Minimum 3 participants to activate challenge
- If < 3 by tap-in deadline: auto-cancel, refunds issued

**Active Phase:**
- Participants try to survive (avoid elimination trigger)
- No action required unless eliminated

**Tap Out:**
- Self-report via button/link
- Optional: elimination note ("It was in the supermarket")
- Buy-in forfeited
- Removal is final (no re-entry)

### 4. Resolution

| Scenario | Outcome |
|----------|---------|
| **Last man standing** | Single winner takes full pot |
| **Deadline reached** | Survivors split pot evenly |
| **Creator cancels** | All buy-ins refunded |
| **< 3 participants** | Auto-cancel, buy-ins refunded |
| **Everyone eliminated** | Pot returned to system (no winners) |

### 5. Creator Privileges

- **Can participate:** Yes (unlike Super Challenge)
- **Can cancel:** Yes, if challenge becomes redundant
  - All active participants receive buy-in refund
  - System contribution returned
- **Cannot:** Tap out others (honor system only)

### 6. Season Integration

When group is in **season mode**:
- Deadline defaults to season end date
- Fits naturally with seasonal competition arc
- Points count toward season standings

---

## Messaging System

### Design Principles

- **Integrated drama:** Messages blend information with social intrigue
- **Not spammy:** Max 1 message per day, most are event-driven
- **Encourages interaction:** Subtle hints at elimination strategies
- **Personalized:** LLM uses context (standings, user history, pot math)

### Message Triggers

| Trigger | Timing | Content |
|---------|--------|---------|
| **Challenge created** | Immediate | Invitation with stakes explanation |
| **Tap-in closes** | On deadline | "X brave souls have entered the arena..." |
| **50% eliminated** | On event | Dramatic announcement + pot math (only if 3+ survivors remain) |
| **2 remaining** | On event | Final duel energy |
| **Elimination** | On tap-out | Announcement + optional note |
| **7 days left** | Scheduled | Stakes reminder |
| **48 hours** | Scheduled | Pressure + subtle scheming hints |
| **24 hours** | Scheduled | Final day drama |
| **6 hours** | Scheduled | Last chance tension |
| **1 hour** | Scheduled | Climax |
| **Victory** | On resolution | Celebration + final standings |

### Message Examples

**Challenge Announcement:**
```
🎯 NEW ELIMINATION CHALLENGE: Wham! Challenge 2025

Survive without hearing "Last Christmas" by Wham! until Dec 24.

📊 Stakes:
• Pot: 1,050 pts
• Buy-in: 52.5 pts
• Current value per survivor: 1,050 pts (if you're the only one...)

⚠️ Warning: Your friends become your enemies. That aux cord at the party? A weapon. Your coworker's Spotify? Suspect.

[Tap In 🎲]

Tap-in closes: Dec 5, 2025
```

**Elimination Announcement:**
```
💀 FALLEN: @Emma has tapped out!

"It was playing in the supermarket. I didn't stand a chance."

📊 Updated standings:
• 7 survivors remain
• Pot value: 150 pts each

The herd thins. 🎯
```

**50% Milestone (only if 3+ remain):**
```
⚔️ HALFWAY POINT

Half the field has fallen. 4 warriors remain:
@Sarah • @Mike • @Tom • @Lisa

Current value: 262 pts each.
But if one more falls... 350 pts each. 🤔

16 days remain. May the strongest ears survive.
```

**48 Hours Countdown:**
```
⏰ 48 HOURS REMAIN

4 survivors. 262 pts on the line for each.

@Mike, you've been suspiciously confident. Perhaps too confident?
@Sarah has a Christmas party tonight. Just saying. 🎄

The endgame approaches. Trust no one.
```

**Victory (Deadline Mode):**
```
🏆 THE WHAM! CHALLENGE IS OVER!

After 24 days of audio vigilance, 3 survivors split 1,050 pts:

🥇 @Sarah: 350 pts - "Noise-canceling headphones. Worth every penny."
🥇 @Tom: 350 pts - "I haven't left my house since November."
🥇 @Mike: 350 pts - "My family knows the consequences."

💀 Fallen heroes:
• @Emma (Day 5) - "Supermarket ambush"
• @John (Day 12) - "My own ringtone betrayed me"
• @Lisa (Day 18) - "Office party. Never had a chance."
• @Jake (Day 22) - "Mom's car. Two days from glory."

Until next year... 🎵
```

**Victory (Last Man Standing):**
```
👑 SOLE SURVIVOR

After 19 days and 7 eliminations, ONE remains:

🏆 @Sarah claims the entire 1,050 pt pot!

"They all underestimated my commitment to silence."

The fallen:
[chronological elimination list with notes]

All hail the champion. 👑
```

---

## Technical Implementation

### Database Schema

#### 1. ChallengeType Enum Extension

```php
// app/Enums/ChallengeType.php
enum ChallengeType: string
{
    case USER_CHALLENGE = 'user_challenge';
    case SUPER_CHALLENGE = 'super_challenge';
    case ELIMINATION_CHALLENGE = 'elimination_challenge'; // NEW
}
```

#### 2. New Enum: EliminationMode

```php
// app/Enums/EliminationMode.php
enum EliminationMode: string
{
    case LAST_MAN_STANDING = 'last_man_standing';
    case DEADLINE = 'deadline';
}
```

#### 3. Challenges Table Additions

```php
// Migration: add_elimination_challenge_support_to_challenges_table
Schema::table('challenges', function (Blueprint $table) {
    // Elimination-specific fields
    $table->string('elimination_trigger')->nullable()
          ->comment('What eliminates participants (e.g., "Hearing Last Christmas")');

    $table->string('elimination_mode')->nullable()
          ->comment('last_man_standing or deadline');

    $table->integer('point_pot')->nullable()
          ->comment('Total pot to distribute to survivors');

    $table->integer('buy_in_amount')->nullable()
          ->comment('Per-person buy-in commitment');

    $table->timestamp('tap_in_deadline')->nullable()
          ->comment('When tap-in registration closes');

    $table->integer('min_participants')->default(3)
          ->comment('Minimum participants to activate');
});
```

#### 4. Challenge Participants Table Additions

```php
// Migration: add_elimination_fields_to_challenge_participants
Schema::table('challenge_participants', function (Blueprint $table) {
    $table->timestamp('tapped_in_at')->nullable();
    $table->timestamp('eliminated_at')->nullable();
    $table->string('elimination_note')->nullable()
          ->comment('Optional self-reported elimination context');

    $table->foreignUuid('buy_in_transaction_id')->nullable()
          ->constrained('transactions')
          ->comment('Reference for refund if cancelled');

    $table->foreignUuid('payout_transaction_id')->nullable()
          ->constrained('transactions')
          ->comment('Reference when prize distributed');
});
```

### New Transaction Types

```php
// app/Enums/TransactionType.php additions
case EliminationBuyIn = 'elimination_buy_in';
case EliminationBuyInRefund = 'elimination_buy_in_refund';
case EliminationPrize = 'elimination_prize';
case EliminationSystemContribution = 'elimination_system_contribution';
```

### Service Layer

```php
// app/Services/EliminationChallengeService.php

class EliminationChallengeService
{
    /**
     * Calculate suggested pot based on group economy
     */
    public function calculateSuggestedPot(Group $group): int
    {
        $totalCurrency = $this->getTotalGroupCurrency($group);
        return (int) round($totalCurrency * 0.10);
    }

    /**
     * Calculate buy-in based on pot and group size
     */
    public function calculateBuyIn(int $pot, int $groupSize): int
    {
        return (int) round(($pot / $groupSize) * 0.5);
    }

    /**
     * Create elimination challenge
     */
    public function createChallenge(
        Group $group,
        User $creator,
        string $name,
        string $eliminationTrigger,
        EliminationMode $mode,
        ?Carbon $deadline,
        int $pot,
        ?Carbon $tapInDeadline = null
    ): Challenge;

    /**
     * User taps in to challenge
     */
    public function tapIn(Challenge $challenge, User $user): ChallengeParticipant;

    /**
     * User taps out (eliminated)
     */
    public function tapOut(
        Challenge $challenge,
        User $user,
        ?string $eliminationNote = null
    ): void;

    /**
     * Cancel challenge and refund all buy-ins
     */
    public function cancel(Challenge $challenge): void;

    /**
     * Check if challenge should auto-cancel (< min participants)
     */
    public function checkAutoCancel(Challenge $challenge): bool;

    /**
     * Resolve challenge and distribute pot
     */
    public function resolve(Challenge $challenge): void;

    /**
     * Get current survivors
     */
    public function getSurvivors(Challenge $challenge): Collection;

    /**
     * Get current pot value per survivor
     */
    public function getPotPerSurvivor(Challenge $challenge): int;

    /**
     * Check if challenge should end (last man standing)
     */
    public function checkLastManStanding(Challenge $challenge): bool;
}
```

### Scheduled Jobs

```php
// app/Console/Kernel.php additions

// Check for deadline-based resolutions
$schedule->job(new ProcessEliminationChallengeDeadlines())
    ->everyFiveMinutes();

// Check for tap-in deadline auto-cancels
$schedule->job(new ProcessEliminationChallengeTapInDeadlines())
    ->everyFiveMinutes();

// Send countdown messages
$schedule->job(new SendEliminationChallengeCountdowns())
    ->hourly();
```

### Event System

```php
// Events
EliminationChallengeCreated::class
EliminationChallengeTappedIn::class
EliminationChallengeActivated::class    // When min participants reached
EliminationChallengeTappedOut::class
EliminationChallengeMilestone::class    // 50%, 2 remaining
EliminationChallengeResolved::class
EliminationChallengeCancelled::class

// Listeners
SendEliminationAnnouncementListener::class
SendEliminationMilestoneMessageListener::class
SendEliminationVictoryMessageListener::class
```

### Web Routes

```php
// routes/web.php additions

Route::middleware(['signed', 'auth.from-signed-url'])->group(function () {
    // Tap in
    Route::get('/elimination/{challenge}/tap-in', [EliminationChallengeController::class, 'showTapIn'])
        ->name('elimination.tap-in');
    Route::post('/elimination/{challenge}/tap-in', [EliminationChallengeController::class, 'tapIn'])
        ->name('elimination.tap-in.submit');

    // Tap out
    Route::get('/elimination/{challenge}/tap-out', [EliminationChallengeController::class, 'showTapOut'])
        ->name('elimination.tap-out');
    Route::post('/elimination/{challenge}/tap-out', [EliminationChallengeController::class, 'tapOut'])
        ->name('elimination.tap-out.submit');

    // Cancel (creator only)
    Route::post('/elimination/{challenge}/cancel', [EliminationChallengeController::class, 'cancel'])
        ->name('elimination.cancel');
});
```

### LLM Integration

```php
// Message context for LLM
$context = [
    'challenge_name' => $challenge->name,
    'elimination_trigger' => $challenge->elimination_trigger,
    'mode' => $challenge->elimination_mode,
    'deadline' => $challenge->deadline,
    'total_pot' => $challenge->point_pot,
    'survivors' => $survivors->map(fn($s) => [
        'name' => $s->user->display_name,
        'points_balance' => $s->user->getBalance($group),
        'days_survived' => $s->tapped_in_at->diffInDays(now()),
    ]),
    'eliminated' => $eliminated->map(fn($e) => [
        'name' => $e->user->display_name,
        'eliminated_at' => $e->eliminated_at,
        'note' => $e->elimination_note,
        'days_survived' => $e->tapped_in_at->diffInDays($e->eliminated_at),
    ]),
    'pot_per_survivor' => $this->getPotPerSurvivor($challenge),
    'days_remaining' => $challenge->deadline?->diffInDays(now()),
    'message_type' => $messageType, // announcement, elimination, milestone, countdown, victory
];
```

---

## User Stories

### As a participant
- I want to join survival challenges that run over multiple days
- I want to see who's still in the competition
- I want dramatic updates as others get eliminated
- I want to feel the tension as the pot grows per survivor

### As a creator
- I want to create fun elimination challenges for my group
- I want to participate in my own challenge (unlike Super Challenge)
- I want to cancel if the challenge becomes irrelevant
- I want to set appropriate deadlines and stakes

### As an eliminated participant
- I want to share my elimination story (optional note)
- I want to see the final outcome even though I'm out
- I want to root for/against remaining survivors

---

## Success Metrics

### Engagement
- **Tap-in rate:** >50% of group members join elimination challenges
- **Completion rate:** >30% of participants survive to resolution
- **Note submission:** >40% of eliminations include a note

### Retention
- **Session frequency:** Users check status 2-3x per challenge duration
- **Message engagement:** >60% of countdown messages read within 2h
- **Repeat creation:** Groups create 2+ elimination challenges per season

### Economy
- **Pot sizing:** Average pot = 8-12% of group currency
- **Point creation:** Controlled inflation (system matches ~50% of pot)

---

## Key Decisions Made

### 1. Participation Model
**Decision:** Creator CAN participate (unlike Super Challenge)

**Rationale:**
- Elimination challenges are social games, not task validation
- No conflict of interest (no creator validation needed)
- More fun when creator has skin in the game

### 2. Point Economics
**Decision:** Buy-in + system match

- Pot = 10% of group currency (suggested, adjustable)
- Buy-in = 50% of fair share
- System matches to reach full pot

**Rationale:**
- Skin in the game (buy-in)
- Attractive pot size (system contribution)
- Controlled inflation

### 3. Verification
**Decision:** Honor system (self-report elimination)

**Rationale:**
- Matches existing group trust dynamics
- No practical way to verify most triggers
- Social pressure prevents cheating
- Cheating ruins the fun (self-regulating)

### 4. Elimination Note
**Decision:** Optional, feeds LLM context

**Rationale:**
- Adds storytelling value
- Makes announcements more engaging
- Not mandatory (low friction)

### 5. Re-entry
**Decision:** No re-entry allowed

**Rationale:**
- Keeps stakes meaningful
- Simpler implementation
- Prevents gaming the system

### 6. Minimum Participants
**Decision:** 3 minimum to activate

**Rationale:**
- 2-person challenges aren't elimination-style
- Ensures meaningful pot split dynamics
- Auto-cancels if not reached (refunds issued)

### 7. Season Integration
**Decision:** Deadline defaults to season end

**Rationale:**
- Natural fit with seasonal competition
- Reduces creator decision burden
- Creates season-long engagement arc

### 8. Messaging Frequency
**Decision:** Event-driven + countdown schedule (not daily random)

**Rationale:**
- Milestones: on elimination events
- Countdown: 7d, 48h, 24h, 6h, 1h
- Social hints integrated into countdown (not separate)
- Prevents message fatigue

### 9. 50% Milestone
**Decision:** Only trigger if 3+ survivors remain

**Rationale:**
- With 5 participants, 50% = 2-3 remaining
- Overlaps with "2 remaining" message
- Only meaningful with larger groups

---

## Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| **Cheating** (not tapping out when eliminated) | Medium | Low | Social pressure, group trust, no prize if caught |
| **Early mass elimination** (challenge ends day 1) | Low | Low | Pot still distributes, interesting story |
| **No one taps in** | Low | Medium | Auto-cancel with refunds, adjust pot suggestion |
| **Creator abuse** (cancels when losing) | Medium | Low | Track cancel patterns, social accountability |
| **Inflation spiral** | Medium | Low | System contribution capped, seasonal resets |
| **Message fatigue** | Low | Medium | Limited schedule, event-driven only |

---

## Implementation Plan

### Phase 1: Core Mechanics (4-5 hours)
1. Database migrations (challenges, participants)
2. New enums (EliminationMode, TransactionTypes)
3. `EliminationChallengeService` core methods
4. Tap-in/tap-out flows

### Phase 2: Resolution & Economics (3-4 hours)
1. Pot calculation and distribution
2. System contribution logic
3. Auto-cancel for < min participants
4. Deadline resolution job

### Phase 3: Messaging (2-3 hours)
1. Countdown message scheduling
2. Milestone detection and messaging
3. LLM prompt templates
4. Victory announcements

### Phase 4: Web Forms (1-2 hours)
1. Add to `/newchallenge` form flow
2. Tap-in confirmation page
3. Tap-out form with optional note
4. Challenge status view

**Total estimated:** 10-14 hours

---

## Future Enhancements

### Variant Modes
- **Team elimination:** Teams instead of individuals
- **Immunity challenges:** Mini-tasks to gain immunity for 24h
- **Bounty mode:** Points for "assisting" eliminations

### Gamification
- **Survival badges:** "Survived 5 elimination challenges"
- **Elimination leaderboard:** Most dramatic tap-outs
- **Saboteur badge:** Most eliminations "influenced"

### Advanced Mechanics
- **Blind buy-in:** Don't know pot size until tap-in closes
- **Escalating stakes:** Buy-in increases each day you survive
- **Resurrection:** Pay 2x buy-in to re-enter (once per challenge)

---

## Appendix: LLM Prompt Templates

### Elimination Announcement
```
Generate a dramatic elimination announcement for an Elimination Challenge.

Context:
- Challenge: {challenge_name}
- Eliminated: {user_name}
- Elimination note: {note or "No note provided"}
- Days survived: {days}
- Remaining survivors: {count}
- New pot per survivor: {amount}

Style: Dramatic sports commentary meets reality TV. Short, punchy. Include pot math.
Max length: 150 words.
```

### Countdown Message
```
Generate a countdown message for an Elimination Challenge.

Context:
- Challenge: {challenge_name}
- Time remaining: {hours/days}
- Survivors: {list with names and current standings}
- Pot per survivor: {amount}
- Recent eliminations: {list}

Style: Build tension. Hint at social dynamics. Reference specific survivors.
Include subtle encouragement for "strategic thinking."
Max length: 100 words.
```

### Victory Announcement
```
Generate a victory announcement for an Elimination Challenge.

Context:
- Challenge: {challenge_name}
- Mode: {last_man_standing or deadline}
- Winners: {list with names and winnings}
- Total duration: {days}
- Elimination timeline: {chronological list with notes}

Style: Epic, celebratory. Honor the winners. Tell the story of the fallen.
Include memorable quotes from elimination notes.
Max length: 200 words.
```

---

**Next Steps:**
1. Confirm design decisions
2. Create implementation tasks
3. Begin Phase 1 development

**Dependencies:** Existing challenge infrastructure, LLM messaging service
**Priority:** Medium-High (high engagement potential, moderate effort)
