# PRD: SuperChallenges - Positive-Sum Group Challenges

**Created:** November 3, 2025
**Status:** Draft - Ideation Phase
**Priority:** Post-MVP Enhancement
**Estimated Effort:** 12-16 hours

---

## TL;DR

**What:** System-facilitated challenges where multiple users compete for "house money" (newly minted points) rather than each other's points.

**Why:** Create positive-sum game dynamics that encourage healthy behaviors and increase overall engagement through collaborative competition.

**How:** Private nudges to random users to create challenges, creator-based validation, proportional prize distribution.

---

## Problem Statement

### Current State
All current game mechanics are **zero-sum**:
- User-to-user wagers: Winner gains exactly what loser loses
- 1v1 challenges: Points transfer between two players
- Group point pool remains constant (excluding decay/bonuses)

### The Gap
- No incentive for **collaborative achievement**
- No mechanism for **group-wide positive reinforcement**
- Limited opportunities for **healthy behavior encouragement**
- Point scarcity can discourage participation over time

### Opportunity
Introduce **positive-sum challenges** where "the house" injects new points into the group economy, creating:
- Shared goals without direct competition
- Reward for healthy/productive behaviors
- Mystery and intrigue (who created this challenge?)
- Inflationary pressure on point pool (encourages bigger future wagers)

---

## Solution Overview

### Core Concept

**SuperChallenge:** A group challenge where:
1. One user (randomly selected) is privately nudged to create a challenge
2. Multiple group members can accept and participate (creator excluded)
3. Winners receive newly minted points (not from other users)
4. Completion is validated by the creator (who acts as referee)
5. Prize pool is split proportionally among all validated completers
6. Creator earns bonus points for engagement and validation activity

### Example Flow

```
[Cron Job] → Group eligible for SuperChallenge
    ↓
[System] → Randomly selects User A (active member)
    ↓
[Private DM to User A] → "Hey! Time for a SuperChallenge. Want to create one?"
    ↓
[User A accepts] → Opens creation form with inspiration examples
    ↓
[User A creates] → "Run 5km in under 30 minutes" (7 day deadline)
    ↓
[System posts to group] → "🏆 NEW SUPERCHALLENGE! Run 5km < 30min. Prize: 100 points per completer (max 10)"
    ↓
[Users B, C, D] → Click "Accept Challenge" button
    ↓
[User A] → Receives +50 point bonus (≥1 acceptance achieved)
    ↓
[3 days later, User B] → Clicks "I Completed This" → Uploads Strava screenshot
    ↓
[System DMs User A] → "User B claims completion. Validate?" [Approve/Reject]
    ↓
[User A] → Clicks "Approve"
    ↓
[User B] → Receives 100 points + [User A] → Receives +25 point validation bonus
    ↓
[Next day, User C] → Also completes and User A validates
    ↓
[User C] → Receives 100 points + [User A] → Receives another +25 validation bonus
    ↓
[User A] → Total earned: 100 points (50 for acceptance + 25 + 25 for validations)
```

---

## Key Features

### 1. Creation Mechanism: Private Nudge System

**Why not fully automated?**
- Groups have different interests (running, cycling, reading, coding)
- AI can't reliably know group context without human input
- Mystery element creates intrigue ("Who created this?")

**How it works:**
1. **Eligibility Check** (Cron job - configurable frequency)
   - Has group's `super_challenge_frequency` interval passed?
   - Default: monthly (configurable: weekly, monthly, quarterly)

2. **User Selection**
   - Random active user (posted/joined wager in last 14 days)
   - User becomes "Creator" and is **excluded from participating**

3. **Private Nudge** (Telegram DM)
   - "🎯 Hey [User]! Your group is ready for a SuperChallenge. Want to create one? (You won't be able to participate, but you'll earn [X] karma points)"
   - Callback buttons: [Create One] [Not Now]

4. **Creation Form** (if accepted)
   - Opens web page (signed URL) with creation form
   - Shows 15 static inspiration examples (fitness, learning, creative, social, gaming)
   - User can click [Use this] to pre-fill or write custom
   - Simple form: `challenge_description` + `deadline` (3/7/14/30 days) + `evidence_guidance` (optional)

5. **Fallback** (if declined)
   - Ask another random user after 24 hours
   - Max 3 attempts per cycle
   - If all decline, skip this cycle

**Database tracking:**
```php
super_challenge_nudges
- id (uuid)
- group_id
- nudged_user_id
- response (pending, accepted, declined, expired)
- nudged_at
- responded_at
```

### 2. Prize Per Person Calculation (Not Split Pot!)

**Key Design:** Each completer receives the SAME amount (not split among completers)

**Formula:**
```php
$totalGroupPoints = $group->users->sum('current_points');
$percentage = rand(2, 5); // Lower than split-pot (was 5-15%)
$prizePerPerson = round(($totalGroupPoints * $percentage / 100) / 50) * 50;

// Per-person bounds
$prizePerPerson = max(50, min(150, $prizePerPerson));

// Global safety: never mint more than 1,000 pts total per SuperChallenge
$maxTotalPrize = 1000;
$maxParticipants = floor($maxTotalPrize / $prizePerPerson);
$maxParticipants = min(10, $maxParticipants); // Cap at 10 people
```

**Examples:**
- Group: 5,000 pts → 4% → 200 → clamped to 150 per person, max 6 participants (900 total)
- Group: 10,000 pts → 3% → 300 → clamped to 150 per person, max 6 participants (900 total)
- Group: 2,000 pts → 5% → 100 per person, max 10 participants (1,000 total)
- Group: 1,000 pts → 3% → 30 → clamped to 50 per person, max 10 participants (500 total)

**Announcement Example:**
```
🏆 NEW SUPERCHALLENGE!
Run 5km in under 30 minutes

Prize: 100 points per completer
Max participants: 10
Deadline: 7 days
```

**Rationale:**
- **Collaborative:** Everyone roots for each other (no competition between participants)
- **Simple settlement:** No recalculation or transaction reversals needed!
- **Clear messaging:** Participants know exactly what they'll earn
- **Inflation control:** Participant cap + total minting cap (max 1,000 pts)
- **Scales fairly:** Lower % keeps it proportional to group size

### 3. Multi-Participant System

**Unlike 1v1 challenges:**
- **Multiple acceptances** allowed (capped at max participants calculated above)
- **Each completer gets the FULL per-person prize** (no splitting!)
- **No direct competition** between participants (truly cooperative)

**Participation tracking:**
```php
challenge_participants
- id (uuid)
- challenge_id
- user_id
- accepted_at
- completed_at (nullable)
- evidence (nullable) // screenshot URL, description
- validation_status (pending, validated, rejected)
- validated_by_creator_at (timestamp, nullable)
```

**Edge cases:**
- **No participants accept:** Challenge expires, no points minted, creator gets no bonus
- **Participants accept but none complete:** Challenge expires, no points minted, creator keeps acceptance bonus
- **One completer:** Gets full per-person prize (e.g., 100 pts)
- **Multiple completers:** Each gets full per-person prize (e.g., 3 people × 100 pts = 300 pts minted total)
- **Hit participant cap:** "Max participants reached" - no more acceptances allowed

### 4. Creator Validation System

**Why creator validates (not peer-based)?**
- **Creator = Referee:** They created the challenge, they judge completion
- **Simpler:** One decision maker vs complex majority voting
- **Incentivized:** Creator earns +25 points per validation (stays engaged)
- **Familiar pattern:** Similar to how challenge creators settle outcomes
- **Trust-based:** Auto-approves if creator doesn't respond (48h timeout)

**Validation flow:**
1. **Completion claim:**
   - Participant clicks "I Completed This" button (callback to web form)
   - Opens form: Upload evidence (optional), description field
   - Submits claim

2. **Creator notification:**
   - System sends Telegram DM to creator: "🎉 [User] claims completion of your SuperChallenge!"
   - Shows evidence (if provided)
   - Callback buttons: [Approve ✅] [Reject ❌]

3. **Creator decision:**
   - Clicks Approve → Participant receives their share of prize pool
   - Creator earns +25 point validation bonus
   - System announces validation to group

4. **Auto-approval fallback:**
   - If creator doesn't respond within 48 hours → auto-approve
   - Prevents blocking participants due to inactive creator
   - Still gives creator the +25 validation bonus (goodwill)

5. **Simple distribution (no recalculation needed!):**
   - Each validated completer receives the per-person prize immediately
   - Example: User B validated → gets 100 pts. User C validated → also gets 100 pts
   - No transaction reversals needed
   - Creator gets +25 bonus for each validation
   - Total minted scales with completions (up to max participant cap)

**Validation tracking:**
```php
challenge_participants
- validation_status (pending, validated, rejected)
- validated_by_creator_at (timestamp, nullable)
- auto_validated_at (timestamp, nullable) // If timeout triggered
```

### 5. Creation Page UX: Guided Free Text with Static Inspiration

**Problem with LLM suggestions:**
- Cold start (new groups have no history)
- Context limitations (can't know if group is into sports vs books)
- Relevance risk (wildly off suggestions)
- Latency and cost

**Solution: Free text + static diverse examples**

**Page structure:**

```
┌─────────────────────────────────────────────┐
│ 🏆 You've Been Chosen!                      │
│                                             │
│ Create a SuperChallenge for [GroupName]    │
│ Prize Pool: 350 points                      │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ Challenge Description *                     │
│ ┌─────────────────────────────────────────┐ │
│ │ What should your group accomplish?      │ │
│ │                                         │ │
│ └─────────────────────────────────────────┘ │
│ 200 characters max                          │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ Deadline *                                  │
│ ○ 3 days  ● 7 days  ○ 14 days  ○ 30 days   │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ How should people prove completion?         │
│ (Optional guidance for participants)        │
│ ┌─────────────────────────────────────────┐ │
│ │ e.g., "Screenshot from Strava app"      │ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘

                [Create Challenge]

─────────────────────────────────────────────

Need inspiration? Popular SuperChallenges:

🏃 Fitness & Wellness
  • Run 5km in under 30 minutes [Use this]
  • 10,000 steps daily for 7 days [Use this]
  • No alcohol for 7 days [Use this]
  • Meditate for 10 minutes daily for a week [Use this]

📚 Learning & Productivity
  • Read a non-fiction book [Use this]
  • Complete an online course or tutorial [Use this]
  • Learn 50 words in a new language [Use this]
  • Write a blog post or journal entry daily [Use this]

🎨 Creative
  • Take a daily photo with different themes [Use this]
  • Create something with your hands [Use this]
  • Write a short story or poem [Use this]

👥 Social
  • Cook dinner for the group [Use this]
  • Organize a game night [Use this]
  • Try a new restaurant and share photos [Use this]

🎮 Gaming
  • Beat your high score in a game [Use this]
  • Complete a challenging achievement [Use this]
```

**[Use this] buttons:**
- Pre-fill the description field
- Creator can edit as needed
- Evidence guidance auto-populated for common ones

**Static examples (15 total):**
- Covers diverse domains (fitness, learning, creative, social, gaming)
- Universal enough to work for most groups
- Creator knows their group best → will gravitate to relevant category
- No LLM cost/latency
- Always available (no API dependency)

**Why this works:**
✅ Flexible (free text for unique challenges)
✅ Fast (no waiting for LLM)
✅ Inspiring (examples spark ideas)
✅ Simple (just 3 fields)
✅ Universal (works for any group type)

### 6. Creator Bonus Structure

**Engagement-based rewards:**

1. **Creating the challenge:** 0 points
   - Just creating doesn't earn anything
   - **Critical for mystery:** No point change = nobody knows who created it!
   - Prevents spam (must get acceptance to earn)

2. **First participant acceptance:** +50 points
   - Triggers when ≥1 user accepts the challenge
   - **Preserves mystery:** Points awarded only after challenge announced
   - Proves challenge is engaging
   - One-time bonus (not per participant)

3. **Per validation:** +25 points each
   - Earned when creator validates a completion (approve or reject)
   - Keeps creator engaged through lifecycle
   - Auto-approvals (48h timeout) still give creator the bonus (goodwill)
   - No limit (rewards active facilitation)

**Example scenario:**
```
Creator creates challenge → 0 points earned (mystery preserved!)
3 users accept → +50 points (first acceptance bonus)
User A completes, creator validates → +25 points
User B completes, creator validates → +25 points
User C's claim auto-approves (creator offline) → +25 points
Total earned: 125 points (for creating one good challenge)
```

**Rationale:**
- **Preserves mystery:** No point change at creation = intrigue maintained
- **Quality over quantity:** Bonus tied to acceptance rate
- **Active involvement:** Validation bonuses keep creator engaged
- **Fair compensation:** Creator can't participate, so earns through facilitation
- **Prevents spam:** No bonus unless challenge gets traction

### 7. Frequency & Settings

**Group settings (new columns):**
```php
groups
- super_challenge_frequency (enum: weekly, monthly, quarterly)
- last_super_challenge_at (timestamp)
```

**Default:** `monthly`

**Configuration UI:**
- Add to [GroupSettingsForm.vue](../resources/js/Pages/Settings/GroupSettingsForm.vue)
- Radio buttons: Weekly / Monthly / Quarterly
- Show last SuperChallenge date
- Show next eligible date

---

## Technical Implementation

### Database Schema Changes

#### 1. Extend `challenges` table
```php
// Migration: add_super_challenge_support_to_challenges
Schema::table('challenges', function (Blueprint $table) {
    $table->enum('type', ['user_challenge', 'super_challenge'])
          ->default('user_challenge')
          ->after('id');

    $table->integer('prize_per_person')
          ->nullable()
          ->comment('Amount each completer receives (not total pool)')
          ->after('points');

    $table->integer('max_participants')
          ->nullable()
          ->comment('Max number of users who can accept')
          ->after('prize_per_person');

    $table->text('evidence_guidance')
          ->nullable()
          ->comment('Creator guidance on how to prove completion')
          ->after('max_participants');

    $table->uuid('creator_user_id')
          ->nullable()
          ->change(); // Allow NULL for system challenges
});
```

#### 2. New table: `challenge_participants`
```php
Schema::create('challenge_participants', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('challenge_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('accepted_at');
    $table->timestamp('completed_at')->nullable();
    $table->text('evidence')->nullable();
    $table->text('completion_description')->nullable();
    $table->enum('validation_status', ['pending', 'validated', 'rejected'])
          ->default('pending');
    $table->timestamp('validated_by_creator_at')->nullable();
    $table->timestamp('auto_validated_at')->nullable();
    $table->timestamps();

    $table->unique(['challenge_id', 'user_id']);
});
```

#### 3. New table: `super_challenge_nudges`
```php
Schema::create('super_challenge_nudges', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('nudged_user_id')->constrained('users')->cascadeOnDelete();
    $table->enum('response', ['pending', 'accepted', 'declined', 'expired'])
          ->default('pending');
    $table->timestamp('nudged_at');
    $table->timestamp('responded_at')->nullable();
    $table->timestamps();

    $table->index(['group_id', 'nudged_at']);
});
```

#### 4. Extend `groups` table
```php
Schema::table('groups', function (Blueprint $table) {
    $table->enum('super_challenge_frequency', ['weekly', 'monthly', 'quarterly'])
          ->default('monthly')
          ->after('settings');

    $table->timestamp('last_super_challenge_at')
          ->nullable()
          ->after('super_challenge_frequency');
});
```

### Service Layer

#### New: `SuperChallengeService`
```php
namespace App\Services;

class SuperChallengeService
{
    /**
     * Check groups eligible for SuperChallenge and send nudges
     */
    public function processEligibleGroups(): void

    /**
     * Create SuperChallenge from nudged user's input
     */
    public function createSuperChallenge(
        Group $group,
        User $creator,
        string $description,
        int $deadlineDays,
        ?string $evidenceGuidance = null
    ): Challenge

    /**
     * Award creator bonus when first participant accepts
     */
    public function awardAcceptanceBonus(Challenge $challenge): void

    /**
     * User accepts SuperChallenge
     */
    public function acceptChallenge(Challenge $challenge, User $user): void

    /**
     * User claims completion with evidence
     */
    public function claimCompletion(
        Challenge $challenge,
        User $user,
        ?string $evidence
    ): void

    /**
     * Creator validates/rejects a completion claim
     */
    public function validateCompletion(
        ChallengeParticipant $participant,
        User $creator,
        bool $approve
    ): void

    /**
     * Award creator +25 point validation bonus
     */
    public function awardValidationBonus(Challenge $challenge, User $creator): void

    /**
     * Calculate and distribute prize pool
     */
    public function distributePrizePool(Challenge $challenge): void

    /**
     * Calculate prize pool based on group's total points
     */
    private function calculatePrizePool(Group $group): int
}
```

### Jobs & Notifications

#### Cron Jobs
```php
// In App\Console\Kernel
$schedule->job(new ProcessSuperChallengeEligibility())
    ->daily()
    ->at('10:00'); // Check daily, but respects frequency setting
```

#### Queue Jobs
```php
// When SuperChallenge created
SendSuperChallengeAnnouncementJob::dispatch($challenge);

// When user accepts
NotifySuperChallengeAcceptanceJob::dispatch($challenge, $user);

// When user claims completion
NotifyCompletionClaimJob::dispatch($participant);

// When completion validated
DistributeSuperChallengePrizeJob::dispatch($challenge);
```

### Telegram Integration (Reuse Existing Patterns)

#### Callback Buttons
```php
// In SuperChallengeAnnouncementJob
$keyboard = [
    [
        [
            'text' => '✅ Accept Challenge',
            'url' => URL::temporarySignedRoute(
                'superchallenge.accept',
                now()->addDay(),
                ['challenge' => $challenge->id]
            ),
        ],
    ],
];
```

```php
// In CompletionClaimAnnouncementJob
$keyboard = [
    [
        [
            'text' => '✅ Validate',
            'url' => URL::temporarySignedRoute(
                'superchallenge.validate',
                now()->addHours(48),
                ['participant' => $participant->id, 'vote' => 'approve']
            ),
        ],
        [
            'text' => '❌ Reject',
            'url' => URL::temporarySignedRoute(
                'superchallenge.validate',
                now()->addHours(48),
                ['participant' => $participant->id, 'vote' => 'reject']
            ),
        ],
    ],
];
```

### Routes (Web)
```php
Route::middleware(['signed', 'auth.from-signed-url'])->group(function () {
    // Accept SuperChallenge
    Route::get('/superchallenge/{challenge}/accept', [SuperChallengeController::class, 'accept'])
        ->name('superchallenge.accept');

    // Claim completion (show form)
    Route::get('/superchallenge/{challenge}/complete', [SuperChallengeController::class, 'showCompleteForm'])
        ->name('superchallenge.complete.form');

    // Submit completion claim
    Route::post('/superchallenge/{challenge}/complete', [SuperChallengeController::class, 'submitCompletion'])
        ->name('superchallenge.complete.submit');

    // Validate completion
    Route::get('/superchallenge/participant/{participant}/validate', [SuperChallengeController::class, 'validate'])
        ->name('superchallenge.validate');
});

// Private nudge response (from Telegram DM)
Route::middleware(['signed', 'auth.from-signed-url'])->group(function () {
    Route::get('/superchallenge/nudge/{nudge}/respond', [SuperChallengeController::class, 'respondToNudge'])
        ->name('superchallenge.nudge.respond');

    Route::post('/superchallenge/create', [SuperChallengeController::class, 'create'])
        ->name('superchallenge.create');
});
```

### Frontend Components (Inertia + Vue)

#### New pages:
1. **CreateSuperChallenge.vue** (nudged user creates challenge)
   - Hero section: "You've been chosen! Prize pool: [X]"
   - Form fields:
     - Challenge description (textarea, 200 char max)
     - Deadline (radio buttons: 3/7/14/30 days)
     - Evidence guidance (optional textarea)
   - Below form: 15 static inspiration examples with [Use this] buttons
   - Submit creates challenge and posts to group

2. **AcceptSuperChallenge.vue** (user accepts participation)
   - Challenge details (description, deadline, prize pool)
   - Evidence guidance (if provided by creator)
   - Current participants list
   - Accept button (callback from Telegram)

3. **CompleteSuperChallenge.vue** (claim completion)
   - Challenge description reminder
   - Evidence upload field (optional)
   - Description textarea (what you did)
   - Submit sends notification to creator for validation

4. **ValidateCompletion.vue** (creator validates claims)
   - Participant info
   - Evidence display (screenshot/description)
   - Approve/Reject buttons (signed URL from Telegram DM)
   - Shows current validation status
   - Reminds creator of +25 point bonus

#### Modified components:
- **GroupSettingsForm.vue**: Add SuperChallenge frequency setting
- **Dashboard.vue**: Show active SuperChallenges
- **ActivityFeed.vue**: Include SuperChallenge events

---

## User Stories

### As a group member
- I want to participate in group challenges without risking my own points
- I want to see who else is participating in SuperChallenges
- I want to validate my friends' completion claims honestly
- I want to earn bonus points for healthy/productive behaviors

### As a nudged creator
- I want to suggest challenges relevant to my group's interests
- I want to earn karma/recognition for creating engaging challenges
- I don't want to feel pressured if I'm busy (can decline)
- I want to see the mystery/intrigue when group sees the challenge

### As a group admin
- I want to configure how often SuperChallenges appear
- I want to see participation rates over time
- I want to ensure SuperChallenges align with group culture
- I want to prevent abuse (fake validations, collusion)

---

## Success Metrics

### Phase 1 (First Month)
- **Acceptance rate**: >60% of nudged users create challenges
- **Participation rate**: >40% of group members accept challenges
- **Completion rate**: >30% of acceptances result in validated completions
- **Validation activity**: >70% of completions receive votes within 24h

### Phase 2 (Ongoing)
- **Engagement boost**: SuperChallenge weeks see +25% overall platform activity
- **Point inflation**: Average group point pool grows 10-15% monthly (expected)
- **Retention**: Groups with active SuperChallenges have +20% retention vs non-users

---

## ✅ Key Decisions Made

### 1. Creator Incentive ✅ DECIDED
**Decision:** Engagement-based bonus structure

- **+50 points** when ≥1 user accepts challenge (proves engagement)
- **+25 points** per validation (creator validates, reject or approve)
- Auto-approvals (48h timeout) still give creator the +25 bonus

**Rationale:**
- Incentivizes quality (must get acceptance)
- Keeps creator involved through lifecycle
- Fair compensation (creator excluded from participating)

### 2. Validation Approach ✅ DECIDED
**Decision:** Creator validates (not peer-based) with 48h auto-approval

- Creator acts as referee (they created it, they judge it)
- Simpler than majority voting
- Creator earns +25 per validation (incentive to participate)
- Auto-approves after 48h if creator doesn't respond

**Rationale:**
- Reuses existing pattern (challenge settlement)
- One decision maker (no complex voting)
- Keeps creator engaged with bonus

### 3. Creation Page UX ✅ DECIDED
**Decision:** Free text with static inspiration examples (no LLM)

- Simple 3-field form (description, deadline, evidence guidance)
- 15 static diverse examples below (fitness, learning, creative, social, gaming)
- [Use this] buttons to pre-fill form
- Creator knows their group best

**Rationale:**
- No cold start problem
- No LLM cost/latency
- Works for any group type
- Fast and flexible

### 4. Prize Structure ✅ DECIDED
**Decision:** Per-person prize (NOT split pot!)

- Each completer receives the FULL per-person amount (e.g., 100 pts each)
- No recalculation, no transaction reversals
- Participant cap (max 10) + total minting cap (max 1,000 pts)
- Lower base percentage (2-5% instead of 5-15%)

**Rationale:**
- **Simpler:** No complex recalculation logic
- **Collaborative:** Everyone roots for each other (no competition)
- **Clear:** Participants know exactly what they'll earn
- **Controlled:** Caps prevent runaway inflation

### 5. Evidence Requirements ✅ DECIDED
**Decision:** Optional (trust-based)

- Evidence field is optional on completion form
- Creator can reject if suspicious
- Guidance field helps set expectations

**Rationale:**
- Trust-based approach fits group dynamics
- Creator has discretion
- MVP simplicity

### 6. Default Frequency ✅ DECIDED
**Decision:** Monthly (configurable: weekly, monthly, quarterly)

**Rationale:**
- Makes SuperChallenges feel special (not spam)
- Gives time for completion and validation
- Can adjust per group preferences

---

## Remaining Open Questions

### 1. Maximum Participants
**Question:** Should there be a cap on how many can accept?

**Options:**
- A) Unlimited ⭐ **Current approach**
- B) Cap at 10 participants (prevents dilution)
- C) Cap at 50% of group size

**Discussion needed:** Monitor if prize dilution becomes issue (e.g., 20 people split 250 pts = 12 pts each)

### 2. Partial Completion
**Question:** What if someone completes 4.5km instead of 5km?

**Options:**
- A) Strict binary (creator decides approve/reject) ⭐ **Recommended for MVP**
- B) Partial credit (pro-rata distribution)
- C) Configurable per challenge

**Discussion needed:** Binary is simpler, creator has discretion for edge cases

---

## Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| **Inflation spiral** (too many points minted) | High | Medium | Prize pool caps (1,000 max), frequency limits |
| **Validation collusion** (friends always approve) | Medium | Medium | Monitor validation patterns, require majority |
| **Low participation** (nobody accepts) | Medium | Low | LLM suggestions tailored to group, smaller pools |
| **Nudge fatigue** (same users always nudged) | Low | Low | Track nudge history, avoid repeat nudges <3 months |
| **Evidence disputes** (is this screenshot real?) | Medium | Medium | Peer validation, trust-based approach for MVP |
| **Complexity creep** (too many rules) | High | High | Start simple (binary validation, fixed rules) |

---

## Future Enhancements (Post-MVP)

### Tiered SuperChallenges
- **Bronze** (100-300 points): Weekly, easy tasks
- **Silver** (400-700 points): Bi-weekly, moderate tasks
- **Gold** (800-1000 points): Monthly, difficult tasks

### Challenge Templates
Pre-built challenge templates:
- Fitness: "Run 5km", "10k steps/day for 7 days"
- Wellness: "Meditate daily for a week", "No alcohol for 30 days"
- Productivity: "Read a book", "Complete an online course"
- Social: "Organize a group event", "Cook dinner for friends"

### AI Challenge Difficulty Calibration
- LLM analyzes past group behavior
- Suggests difficulty level (easy, medium, hard)
- Adjusts prize pool accordingly

### SuperChallenge Leaderboard
- Track most participations
- Track highest validation rates
- Track best challenge creators

### Seasonal SuperChallenge Series
- Linked challenges (e.g., "Couch to 5k" progression)
- Bonus for completing entire series
- Story arc across multiple challenges

---

## Implementation Plan

### Phase 1: Core Mechanics (8-10 hours)
1. Database migrations (challenges, participants, nudges, group settings)
2. `SuperChallengeService` with core methods
3. Cron job for eligibility checking
4. Prize pool calculation logic
5. Basic acceptance flow

### Phase 2: Validation System (4-5 hours)
1. Completion claim flow
2. Peer validation voting
3. Prize distribution with recalculation
4. Transaction handling (minting points)

### Phase 3: Frontend (3-4 hours)
1. Create, Accept, Complete, Validate pages
2. Group settings UI for frequency
3. Dashboard integration
4. Activity feed events

### Phase 4: Testing & Polish (2-3 hours)
1. Unit tests for service methods
2. Integration tests for full flow
3. Edge case handling (no participants, tie validations)
4. LLM prompt refinement

**Total estimated:** 12-16 hours

---

## Alternative Approaches Considered

### 1. Fully Automated System
**Idea:** AI generates and posts challenges automatically.

**Rejected because:**
- Can't know group context (are they runners? readers? gamers?)
- Removes human element and mystery
- Less engagement (no creator involvement)

### 2. Admin-Only Creation
**Idea:** Only group admins can create SuperChallenges.

**Rejected because:**
- Burdens admins
- Less democratic
- Misses mystery element

### 3. /newsuperchallenge Command
**Idea:** Any user can trigger creation via command.

**Rejected because:**
- Too predictable
- No frequency control
- Could spam group

### 4. Fixed Prize Pools
**Idea:** All SuperChallenges worth same amount (e.g., 200 points).

**Rejected because:**
- Not proportional to group size/economy
- Less exciting (no variability)
- Doesn't scale

---

## Dependencies

### Existing Patterns to Reuse
✅ **Signed URLs**: `AuthenticateFromSignedUrl` middleware
✅ **Callback buttons**: Telegram inline keyboards
✅ **LLM service**: OpenAI integration for suggestions
✅ **Job queuing**: Laravel queues for notifications
✅ **Transaction system**: Point ledger with `balance_after`
✅ **Validation logic**: Similar to event attendance validation

### New Dependencies
- None (uses existing tech stack)

---

## Naming Brainstorm

### Challenge Type Names
- ✅ **SuperChallenge** - Clear hierarchy, playful
- GroupChallenge - Emphasizes participation
- BonusChallenge - Emphasizes reward
- CommunityChallenge - Emphasizes cooperation
- HealthBet / FitBet - Too specific to fitness

**Decision: SuperChallenge** (fun, clear, not domain-specific)

### Database Enum Values
```php
type: 'user_challenge' | 'super_challenge'
validation_status: 'pending' | 'validated' | 'rejected'
response: 'pending' | 'accepted' | 'declined' | 'expired'
```

---

## Success Looks Like

### User Perspective
> "A SuperChallenge just dropped! 300 points to run 5km? I'm in!"
>
> "I just validated John's run - he crushed it! Love seeing the group support each other."
>
> "Got nudged to create a challenge - felt honored! Group loved my idea."

### System Perspective
- Clean integration with existing patterns
- No new authentication or messaging paradigms
- Scales with group size naturally
- Point inflation controlled and intentional

### Business Perspective
- Increases engagement (positive reinforcement)
- Encourages healthy behaviors (good PR)
- Adds variety to platform (not just competitive)
- Low maintenance (peer-validated)

---

## Questions for Discussion

1. **Creator incentive**: Karma points, percentage of pot, or nothing?
2. **Validation threshold**: Simple majority, supermajority, or hybrid?
3. **Prize recalculation**: Claw back from early completers or not?
4. **Evidence requirements**: Mandatory, optional, or configurable?
5. **Naming**: Stick with "SuperChallenge" or alternative?
6. **Frequency default**: Weekly, monthly, or quarterly to start?

---

## Appendix: Sample LLM Prompts

### Challenge Suggestion Prompt
```
Analyze this group's recent activity and suggest 3-5 SuperChallenge ideas:

Group context:
- Recent wagers: [list of wager questions]
- Recent challenges: [list of challenge descriptions]
- Group size: {count} members
- Point pool: {total} points

Generate challenges that:
- Match the group's interests
- Are achievable within 7 days
- Can be peer-validated with simple evidence
- Encourage healthy or productive behaviors

Format each suggestion as:
- Title (short, action-oriented)
- Description (1-2 sentences)
- Suggested deadline (days)
- Evidence type (photo, screenshot, text description)

Examples:
- "Run 5km in under 30 minutes" (Fitness, 7 days, Strava screenshot)
- "Read a non-fiction book" (Productivity, 14 days, Book cover + summary)
- "Cook a new recipe" (Social, 3 days, Photo of dish)
```

---

**Next Steps:**
1. Get feedback on open questions
2. Refine naming and thresholds
3. Create implementation task list
4. Begin Phase 1 development

**Estimated Timeline:** 2-3 weeks (part-time)
**Dependencies:** None (post-MVP feature)
**Priority:** Medium (enhances engagement after core platform stable)
