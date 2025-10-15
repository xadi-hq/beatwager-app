# Product Requirements Document: Social Wagering Platform

**Version:** 1.0  
**Date:** October 13, 2025  
**Status:** Draft

---

## Executive Summary

A social wagering platform that enables friends to create and participate in point-based wagers within their groups. The platform uses existing messaging platforms (starting with Telegram) as the primary interface, with a web application handling complex interactions like wager creation and settlement.

**Core Philosophy:** Keep it simple, social, and fun. No real money, no complex odds calculations, no global leaderboards. Just friends betting points on outcomes they care about.

---

## Product Goals

### Primary Goals
1. Enable frictionless wager creation and participation within existing social channels
2. Maintain fair and transparent settlement processes
3. Keep the experience isolated per group (no cross-contamination)
4. Build platform-agnostic architecture for future expansion

### Non-Goals (MVP)
- Real money transactions
- Automated outcome detection (sports APIs, etc.)
- Global leaderboards or cross-group competition
- Complex odds calculation or variable stakes per participant
- Mobile native apps (web-first approach)

---

## User Personas

### The Creator
- Initiates wagers within their friend group
- Wants quick, simple wager creation
- Responsible for settling outcomes
- May manage multiple active wagers

### The Participant
- Joins wagers created by friends
- Wants to track their point balance and active wagers
- Needs visibility into pending settlements
- Wants to dispute unfair settlements

### The Administrator (Future)
- Group moderator or platform admin
- Resolves disputes
- Can adjust points manually if needed
- Reviews flagged settlements

---

## User Stories

### Wager Creation
```
AS A user in a Telegram group
WHEN I type /newbet
THEN I receive a private one-time URL to create a wager

AS A creator on the web interface
WHEN I fill out the wager form
THEN the wager is posted to my group with join buttons

AS A creator
WHEN I select a template
THEN the form pre-fills with sensible defaults I can customize
```

### Participation
```
AS A group member
WHEN I see a new wager announcement
THEN I can join by clicking a button (or replying with my answer)

AS A participant
WHEN I join a wager
THEN my points are reserved and I receive confirmation

AS A participant
WHEN the betting deadline passes
THEN I can no longer join the wager
```

### Settlement
```
AS A creator
WHEN a wager deadline passes
THEN I receive a one-time URL to settle the outcome

AS A creator on the settlement page
WHEN I select the winning outcome
THEN all participants are notified and points are distributed

AS A participant
WHEN I believe a settlement is unfair
THEN I can flag it for dispute

AS A participant in a disputed wager
WHEN enough people also dispute
THEN a majority vote determines the final outcome
```

### Point Management
```
AS A participant
WHEN I participate in any wager during a week
THEN I receive 50 bonus points at week's end

AS A user
WHEN I log into the web interface
THEN I can see my point balance, active wagers, and history
```

---

## Features & Requirements

### Phase 1: MVP

#### 1. Wager Creation (Web)
**Must Have:**
- One-time token authentication from messenger
- Question input (text, 200 char max)
- Type selection: Binary, Numeric, Multiple Choice
- Deadline picker (date + time)
- Stake input (points required to join)
- Group pre-selection (encoded in token)
- Preview of how wager will appear in messenger

**Type-Specific Fields:**
- **Binary:** None additional
- **Numeric:** Optional unit label (e.g., "goals", "days", "degrees")
- **Multiple Choice:** Dynamic option builder (min 2, max 10 options)

**Templates:**
- Sports Match: "Will {team_a} beat {team_b}?"
- Yes/No: "Will {event} happen by {deadline}?"
- Numeric Prediction: "How many {unit} will {subject} achieve?"
- First To: "Who will {achievement} first?"

**Validation:**
- Stake must be ≤ creator's available points
- Deadline must be future date
- Multiple choice requires ≥2 unique options
- Question required, non-empty

#### 2. Wager Announcement (Messenger)
**Must Have:**
- Posted to originating group
- Clear question display
- Shows: stake, deadline, type, current participants
- Join button (or reply instruction for numeric)
- Link to web view for full details

**Format Example (Telegram):**
```
🎲 New Wager from @username

Will Ajax beat PSV on Saturday?

💰 Stake: 100 points
⏰ Closes: Oct 15, 6:00 PM
📊 Type: Yes/No

Participants (0):

[Yes - 100pts] [No - 100pts]
```

#### 3. Joining Wagers (Messenger)
**Must Have:**
- Inline buttons for binary/multiple choice
- Reply parsing for numeric answers
- Instant feedback (success/error message)
- Point validation (sufficient balance)
- Show updated participant list after join

**Edge Cases:**
- User already joined → "You've already placed your bet"
- Insufficient points → "You need {X} more points"
- Deadline passed → "Betting is closed"
- Duplicate numeric answer → Allow (ties split winnings)

#### 4. Settlement (Web)
**Must Have:**
- One-time token auth for creator
- Display full wager details + all entries
- Outcome selection interface:
  - Binary: Yes/No buttons
  - Numeric: Input actual value, system calculates closest
  - Multiple Choice: Click winning option
- Confirmation step with winner preview
- Submit triggers notifications + point distribution

**Point Distribution:**
- Binary/Multiple Choice: Winner pool splits loser contributions proportionally
- Numeric: Closest guess wins all, ties split evenly
- Formula: `points_won = (total_pool / winners_count)`

#### 5. Dispute Flow (Web + Messenger)
**Must Have:**
- Dispute button on settlement notification (24hr window)
- Dispute requires reason (text input, 200 char max)
- If ≥30% of participants dispute → triggers vote
- Majority vote (>50%) can overturn outcome
- Revote triggers new settlement by original creator

**States:**
- `settled` → normal end state
- `disputed` → under dispute review
- `voting` → active vote in progress
- `overturned` → vote succeeded, creator must resettle

#### 6. Web Interface (Authenticated)
**Must Have:**
- Login via Telegram OAuth (or email magic link)
- Dashboard showing:
  - Current point balance (per group)
  - Active wagers (pending, awaiting settlement)
  - Wager history (won/lost/settled)
  - Weekly participation bonus status
- Navigation to create new wager
- View individual wager details

**Roles:**
- `participant`: Can view own wagers, join, dispute
- `creator`: Can create, settle own wagers
- `admin`: Can settle any wager, adjust points, resolve disputes (future)

### Phase 2: Enhancements (Post-MVP)
- Wager editing (before anyone joins)
- Wager cancellation (refunds stakes)
- Event attendance streaks (consecutive events attended)
- Event-specific wagers (meta-wagers about the event itself) >> See near EOF FIRST_CHAT.md
- Betting closes earlier than deadline (separate from main deadline)
- Range-based numeric wagers (within X% wins)
- Advanced season features (mid-season point injections, mini-seasons)

### Phase 3: Future Exploration (Parking Lot)
- Multiple messenger platform support (Slack, Discord, WhatsApp)
- Group challenges (monthly participation goals with rewards)
- Revenge wagers (quick-create after losing to someone)
- Wager analytics (win rate, favorite types, earnings over time)
- Social features (wager feed, trending wagers across groups)
- Advanced dispute resolution (admin panel, mediation tools)
- Automated outcome detection (sports APIs for specific wager types)
- Mobile native apps (if web mobile experience insufficient)

---

## Technical Architecture

### Tech Stack
**Backend:**
- PHP Laravel (latest stable)
- PostgreSQL database
- Redis for caching/queues
- Laravel Queues for async jobs
- Laravel Events for loose coupling

**Frontend:**
- Vue 3 (Composition API, `<script setup>`)
- TypeScript (gradual adoption)
- Tailwind CSS
- Inertia.js v2 (Laravel ↔ Vue bridge)

**Messaging:**
- Telegram Bot API (webhook mode)
- Platform abstraction layer for future expansion

**Hosting:**
- Backend: VPS or cloud
- Database: Managed PostgreSQL
- Queue worker: Supervisor on same/separate instance

### Database Schema

```sql
-- Users and Authentication
users
  id (uuid, primary key)
  telegram_id (bigint, nullable, unique)
  username (string, nullable)
  email (string, nullable, unique)
  created_at, updated_at

-- Groups (Telegram chats, Slack channels, etc.)
groups
  id (uuid, primary key)
  platform (enum: telegram, slack, web)
  platform_group_id (string, unique)
  name (string)
  default_starting_points (integer, default: 1000)
  created_at, updated_at

-- User-Group Relationships
user_group
  id (uuid, primary key)
  user_id (uuid, foreign key)
  group_id (uuid, foreign key)
  current_points (integer, default: 1000)
  role (enum: participant, creator, admin - default: participant)
  joined_at (timestamp)
  last_wager_joined_at (timestamp, nullable) -- for decay tracking
  last_decay_applied_at (timestamp, nullable) -- for decay tracking
  unique(user_id, group_id)

-- Wagers
wagers
  id (uuid, primary key)
  group_id (uuid, foreign key)
  creator_id (uuid, foreign key)
  question (text)
  type (enum: binary, numeric, multiple_choice)
  options (jsonb, nullable) -- for multiple_choice: ["Option A", "Option B"]
  stake (integer) -- points required to join
  deadline (timestamp) -- when outcome should be known
  betting_closes_at (timestamp, nullable) -- optional earlier close
  actual_outcome (jsonb, nullable) -- stores result after settlement
  settled_at (timestamp, nullable)
  settled_by_user_id (uuid, foreign key, nullable)
  status (enum: open, closed, settling, settled, disputed, voting)
  created_at, updated_at
  
  index(group_id, status)
  index(deadline)

-- Wager Entries (individual bets)
wager_entries
  id (uuid, primary key)
  wager_id (uuid, foreign key)
  user_id (uuid, foreign key)
  position (string, nullable) -- "yes"/"no" for binary
  answer_numeric (decimal, nullable) -- for numeric type
  answer_option (string, nullable) -- for multiple_choice
  points_wagered (integer)
  points_won (integer, nullable) -- calculated after settlement
  joined_at (timestamp)
  
  unique(wager_id, user_id)
  index(wager_id)

-- One-Time Tokens
one_time_tokens
  token (string, primary key)
  user_id (uuid, foreign key)
  group_id (uuid, foreign key, nullable)
  wager_id (uuid, foreign key, nullable)
  action (enum: create_wager, settle_wager, join_wager)
  platform (enum: telegram, slack, web)
  expires_at (timestamp)
  used_at (timestamp, nullable)
  
  index(token, used_at)

-- Disputes
disputes
  id (uuid, primary key)
  wager_id (uuid, foreign key)
  disputing_user_id (uuid, foreign key)
  reason (text)
  status (enum: pending, voting, resolved)
  resolution (enum: upheld, overturned, null)
  created_at, updated_at
  
  index(wager_id, status)

-- Dispute Votes
dispute_votes
  id (uuid, primary key)
  dispute_id (uuid, foreign key)
  user_id (uuid, foreign key)
  vote (enum: uphold, overturn)
  created_at
  
  unique(dispute_id, user_id)

-- Point Transactions (audit trail)
transactions
  id (uuid, primary key)
  user_id (uuid, foreign key)
  group_id (uuid, foreign key)
  amount (integer) -- positive or negative
  type (enum: wager_join, wager_win, wager_loss, wager_refund, weekly_bonus, decay_penalty, event_attendance_bonus, admin_adjustment)
  reference_id (uuid, nullable) -- wager_id, event_id, dispute_id, etc.
  balance_after (integer)
  created_at
  
  index(user_id, group_id, created_at)
  index(reference_id, type)

-- Group Seasons
group_seasons
  id (uuid, primary key)
  group_id (uuid, foreign key)
  name (string)
  description (text, nullable)
  starts_at (timestamp)
  ends_at (timestamp)
  prize_description (text, nullable) -- freeform, group defines
  point_value_description (text, nullable) -- e.g., "100pts = €1"
  reset_points_at_start (boolean, default: false)
  entry_deadline (timestamp, nullable)
  status (enum: upcoming, active, ended)
  created_at, updated_at
  
  index(group_id, status)
  index(ends_at)

-- Group Events
group_events
  id (uuid, primary key)
  group_id (uuid, foreign key)
  name (string)
  description (text, nullable)
  event_date (timestamp)
  location (string, nullable)
  attendance_bonus (integer)
  rsvp_deadline (timestamp, nullable)
  auto_prompt_hours_after (integer, default: 2) -- when to ask for attendance
  status (enum: upcoming, completed, expired, cancelled)
  created_by_user_id (uuid, foreign key)
  created_at, updated_at
  
  index(group_id, status)
  index(event_date)

-- Event RSVPs
group_event_rsvps
  id (uuid, primary key)
  event_id (uuid, foreign key)
  user_id (uuid, foreign key)
  response (enum: going, maybe, not_going)
  created_at, updated_at
  
  unique(event_id, user_id)

-- Event Attendance
group_event_attendance
  id (uuid, primary key)
  event_id (uuid, foreign key)
  user_id (uuid, foreign key)
  attended (boolean)
  reported_by_user_id (uuid, foreign key)
  reported_at (timestamp)
  is_challenged (boolean, default: false)
  is_locked (boolean, default: false) -- after 24hr challenge window
  bonus_awarded (boolean, default: false)
  
  unique(event_id, user_id)
  index(event_id, attended)

-- Event Attendance Challenges
group_event_challenges
  id (uuid, primary key)
  event_id (uuid, foreign key)
  user_id (uuid, foreign key) -- person challenging
  challenged_user_id (uuid, foreign key) -- person whose attendance is disputed
  reason (text)
  status (enum: pending, voting, upheld, dismissed)
  created_at, updated_at, resolved_at (nullable)
  
  index(event_id, status)

-- Event Challenge Votes
group_event_challenge_votes
  id (uuid, primary key)
  challenge_id (uuid, foreign key)
  user_id (uuid, foreign key) -- voter
  vote (boolean) -- true = was_present, false = was_not_present
  created_at
  
  unique(challenge_id, user_id)

-- Wager Templates
wager_templates
  id (uuid, primary key)
  name (string)
  description (text, nullable)
  type (enum: binary, numeric, multiple_choice)
  question_template (text) -- e.g., "Will {team_a} beat {team_b}?"
  placeholders (jsonb) -- ["team_a", "team_b"]
  default_stake (integer, nullable)
  default_options (jsonb, nullable) -- for multiple_choice
  is_active (boolean, default: true)
  usage_count (integer, default: 0)
  created_at, updated_at
```

### API Routes

```php
// Public (Webhook)
POST /api/telegram/webhook
POST /api/slack/webhook // future

// Authenticated Web
GET  /wagers/create?token={token}
POST /wagers/create
GET  /wagers/{wager}
GET  /wagers/{wager}/settle?token={token}
POST /wagers/{wager}/settle

GET  /dashboard
GET  /wagers
GET  /wagers/{wager}/dispute
POST /wagers/{wager}/dispute
POST /disputes/{dispute}/vote

GET  /templates
GET  /templates/{template}

GET  /seasons
POST /seasons
GET  /seasons/{season}
PUT  /seasons/{season}
POST /seasons/{season}/end

GET  /events
POST /events
GET  /events/{event}
PUT  /events/{event}
GET  /events/{event}/attendance?token={token}
POST /events/{event}/attendance
POST /events/{event}/rsvp
GET  /events/{event}/challenges/{challenge}
POST /events/{event}/challenges
POST /challenges/{challenge}/vote

// Admin (future)
GET  /admin/groups
GET  /admin/groups/{group}/wagers
POST /admin/groups/{group}/adjust-points
GET  /admin/disputes
POST /admin/disputes/{dispute}/resolve
```

### Service Layer Architecture

```
App/Services/
├── WagerService.php
│   ├── createWager(array $data, User $creator, Group $group)
│   ├── joinWager(Wager $wager, User $user, array $answer)
│   ├── settleWager(Wager $wager, User $settler, $outcome)
│   ├── calculateWinnings(Wager $wager)
│   └── canUserJoin(Wager $wager, User $user): bool
│
├── PointService.php
│   ├── getUserBalance(User $user, Group $group): int
│   ├── reservePoints(User $user, Group $group, int $amount)
│   ├── distributeWinnings(Wager $wager, array $winners)
│   ├── refundStakes(Wager $wager)
│   ├── grantWeeklyBonus(User $user, Group $group)
│   └── applyDecay(User $user, Group $group)
│
├── SeasonService.php
│   ├── createSeason(Group $group, array $data)
│   ├── startSeason(Season $season)
│   ├── endSeason(Season $season)
│   ├── getLeaderboard(Season $season): Collection
│   └── calculateStandings(Season $season): array
│
├── EventService.php
│   ├── createEvent(Group $group, array $data)
│   ├── recordAttendance(Event $event, User $reporter, array $attendees)
│   ├── challengeAttendance(Event $event, User $challenger, User $disputed, string $reason)
│   ├── voteOnChallenge(Challenge $challenge, User $voter, bool $vote)
│   ├── resolveChallenge(Challenge $challenge)
│   └── awardEventBonuses(Event $event)
│
├── DisputeService.php
│   ├── createDispute(Wager $wager, User $user, string $reason)
│   ├── triggerVote(Dispute $dispute)
│   ├── castVote(Dispute $dispute, User $user, string $vote)
│   └── resolveDispute(Dispute $dispute)
│
├── TokenService.php
│   ├── generateToken(User $user, string $action, array $context)
│   ├── validateToken(string $token): OneTimeToken
│   └── consumeToken(string $token)
│
└── Messaging/
    ├── MessengerInterface.php
    ├── TelegramMessenger.php
    ├── SlackMessenger.php // future
    └── MessengerFactory.php
```

### Messenger Interface

```php
interface MessengerInterface
{
    // Outbound messages
    public function sendMessage(string $chatId, string $text, ?array $buttons = null): void;
    public function sendPrivateMessage(string $userId, string $text): void;
    public function updateMessage(string $messageId, string $text, ?array $buttons = null): void;
    
    // Message formatting
    public function formatWagerAnnouncement(Wager $wager): array;
    public function formatSettlementNotification(Wager $wager, array $winners): array;
    
    // Platform-specific
    public function getPlatformName(): string;
}
```

### Events & Listeners

```php
Events:
- WagerCreated → SendWagerAnnouncement
- WagerJoined → UpdateWagerMessage, NotifyCreator
- WagerSettled → DistributePoints, NotifyParticipants
- DisputeCreated → NotifyCreator, NotifyOtherParticipants
- DisputeVotingStarted → NotifyAllParticipants
- DisputeResolved → NotifyAllParticipants, UpdateWagerStatus

- SeasonStarted → NotifyGroupMembers, ResetPointsIfConfigured
- SeasonEnded → FreezeStandings, NotifyGroupWithLeaderboard

- EventCreated → SendEventAnnouncement
- EventAttendanceRecorded → AwardBonuses, NotifyAttendees, PostToGroup
- EventAttendanceChallenged → NotifyAttendees, InitiateVoting
- EventChallengeResolved → ApplyPenalties, UpdateAttendance, NotifyGroup

Jobs (Queued):
- ProcessWagerSettlement
- DistributeWeeklyBonuses (scheduled, weekly - Sunday 11:59 PM)
- ApplyPointDecay (scheduled, daily - 1:00 AM)
- CleanupExpiredTokens (scheduled, daily - 2:00 AM)
- SendDeadlineReminders (scheduled, hourly check)
- SendEventAttendancePrompts (scheduled, hourly check - based on event time + auto_prompt_hours)
- LockEventAttendance (scheduled, hourly check - 24hrs after recording)
```

### Frontend Structure (Vue)

```
resources/js/
├── Pages/
│   ├── Dashboard.vue
│   ├── Wagers/
│   │   ├── Index.vue
│   │   ├── Show.vue
│   │   ├── Create.vue
│   │   └── Settle.vue
│   ├── Disputes/
│   │   └── Show.vue
│   ├── Seasons/
│   │   ├── Index.vue
│   │   ├── Show.vue (leaderboard)
│   │   └── Create.vue
│   └── Events/
│       ├── Index.vue
│       ├── Show.vue
│       ├── Create.vue
│       ├── RecordAttendance.vue
│       └── ChallengeAttendance.vue
│
├── Components/
│   ├── Wager/
│   │   ├── WagerForm.vue
│   │   ├── TemplateSelector.vue
│   │   ├── QuestionInput.vue
│   │   ├── TypeSelector.vue
│   │   ├── DeadlinePicker.vue
│   │   ├── StakeInput.vue
│   │   ├── OptionBuilder.vue (multiple choice)
│   │   ├── NumericSettings.vue
│   │   └── WagerPreview.vue
│   │
│   ├── Settlement/
│   │   ├── BinarySettlement.vue
│   │   ├── NumericSettlement.vue
│   │   └── MultipleChoiceSettlement.vue
│   │
│   ├── Season/
│   │   ├── SeasonForm.vue
│   │   ├── Leaderboard.vue
│   │   └── SeasonCard.vue
│   │
│   ├── Event/
│   │   ├── EventForm.vue
│   │   ├── EventCard.vue
│   │   ├── RsvpButtons.vue
│   │   ├── AttendanceChecklistForm.vue
│   │   └── ChallengeForm.vue
│   │
│   └── Shared/
│       ├── PointBadge.vue
│       ├── WagerCard.vue
│       └── UserAvatar.vue
│
└── Composables/
    ├── useWager.ts
    ├── usePoints.ts
    ├── useSeason.ts
    ├── useEvent.ts
    └── useAuth.ts
```

---

## Business Rules & Constraints

### Point Economy
1. **Starting Balance:** 1,000 points per user per group
2. **Weekly Bonus:** 50 points if participated in ≥1 wager that week
3. **No Credit Purchasing:** Cannot buy more points (keeps scarcity)
4. **Group Isolation:** Points are separate per group
5. **Minimum Stake:** 10 points (prevent spam wagers)
6. **Maximum Stake:** Cannot exceed creator's available balance
7. **Cannot go negative:** Joining validates sufficient balance

### Point Decay System
**Purpose:** Maintain engagement and prevent point hoarding without participation.

**Mechanics:**
1. **Grace period:** First 14 days after joining group (no decay)
2. **Activity tracking:** Joining any wager resets decay timer
3. **Decay trigger:** If no wagers joined in 14+ days
4. **Decay rate:** 5% of current balance per week (min 50pts, max 100pts)
5. **Warning notification:** Sent on day 12 of inactivity
6. **Decay application:** Applied weekly on day 21, 28, 35, etc.

**Example Flow:**
- User has 1,000 points
- Doesn't join any wagers for 15 days
- Day 12: Receives warning notification
- Day 21: Loses 50 points (5% of 1,000, capped at min)
- Day 28: Loses another 50 points (5% of 950 = 47.5, rounded to min 50)
- Day 30: Joins a wager → decay stops, timer resets

**Rationale:** 
- Encourages minimum engagement (once every 2 weeks)
- Prevents "bank and wait" strategy
- Makes active players' points relatively more valuable
- Light enough penalty to avoid feeling punishing

**Technical Implementation:**
- Scheduled job: `ApplyPointDecay` runs daily at 1 AM
- Queries users where `last_wager_joined_at < now() - 14 days`
- Creates `decay_penalty` transaction
- Sends notification via messenger with suggestions to join available wagers

### Group Seasons (Optional)
**Purpose:** Give points meaningful value and create long-term competition narrative.

**What are Seasons?**
Self-organized competition periods where groups define their own stakes and prizes. The platform tracks points and standings but **NEVER handles money or prize distribution**.

**Season Configuration:**
- **Name:** e.g., "2025 Championship", "Summer Showdown"
- **Duration:** Start and end dates (typical: 3-12 months)
- **Prize Description:** Freeform text defined by group (e.g., "Top 3 split €300", "Loser buys dinner")
- **Point Value Description:** Optional context (e.g., "100pts = €1", "1000pts = bragging rights")
- **Reset Points:** Whether to reset all balances at season start
- **Entry Deadline:** Optional cutoff for new participants joining season

**During Active Season:**
- Live leaderboard visible to all group members (web + periodic bot updates)
- Monthly standings notifications via messenger
- Web dashboard displays: rank, points, "equivalent value" (if defined)
- Season progress bar and time remaining
- Top 3 positions highlighted

**Season End:**
- Platform freezes final standings at end date
- Sends comprehensive final leaderboard to group
- **Group handles prize distribution themselves** (Venmo, Tikkie, cash, IOU, etc.)
- Option to archive completed season
- Option to immediately start new season (with/without point reset)

**Platform Liability & Disclaimers:**
The platform explicitly:
- ❌ Does NOT facilitate money transfers
- ❌ Does NOT enforce prize payments
- ❌ Does NOT guarantee prize descriptions
- ❌ Is NOT responsible for disputes about prizes
- ✅ Shows clear disclaimer: "Prizes are organized by your group independently. Platform only tracks points and standings."

**Season Examples:**

*Casual Stakes:*
- "1000 points = one beer. Settle at year-end pub crawl."
- "Winner picks next group vacation destination."

*Competitive Stakes:*
- "Top 3: €150/€100/€50. Bottom 3 contribute €50 each to pool. Settled via Tikkie."

*Non-monetary:*
- "Winner gets permanent bragging rights trophy."
- "Loser does embarrassing task chosen by winner."

**User Stories:**
```
AS A group admin
WHEN I create a season
THEN I can define custom prizes, point values, and duration

AS A participant
WHEN I view the leaderboard during active season
THEN I see my rank, points, and "equivalent value" based on season settings

AS A participant
WHEN season ends
THEN I see final standings and prize information, but handle settlement externally with my group
```

### Group Events
**Purpose:** Reward real-world social engagement, creating bridge between digital competition and physical gatherings.

**Philosophy:** Trust-based system leveraging existing social dynamics rather than technical verification. Uses web interface for all event management while messenger provides notifications only.

**Event Types:**
- **Recurring:** Monthly dinners, weekly game nights, regular meetups
- **One-off:** Trips, special celebrations, annual events

**Event Configuration:**
- **Name:** e.g., "December Dinner", "Summer BBQ"
- **Date & Time:** When event occurs
- **Location:** Optional venue information
- **Attendance Bonus:** Points awarded for verified attendance
- **RSVP Deadline:** Optional cutoff for responses
- **Auto-prompt Timing:** When to ask for attendance (default: 2 hours post-event)

**Event Flow:**

1. **Creation (Web UI)**
   - Admin creates event with details and bonus amount
   - Event posted to group via messenger (announcement only)

2. **RSVP (Optional - Messenger/Web)**
   - Members indicate: Going / Maybe / Can't Make It
   - Purely informational for planning
   - No penalties for RSVP changes or no-shows
   - Updates visible in messenger and web dashboard

3. **Post-Event Attendance Recording (Web)**
   - System posts reminder to group at configured time (e.g., 2 hours after event)
   - Message includes unique URL to attendance form
   - **Any group member** can access URL and submit attendance
   - Web form shows checklist of all group members
   - Submitter selects who attended → submits
   - First complete submission wins
   - Instant point distribution to attendees

4. **Challenge Window (24 hours - Web)**
   - Recorded attendance posted to messenger with challenge link
   - Any member can challenge via web form within 24 hours
   - Challenge requires written reason
   - Triggers voting among recorded attendees
   - Each attendee votes via web link: "Was [person] actually there?"
   - Majority (>50%) decides outcome
   - Results posted back to messenger

5. **Resolution**
   - **Challenge upheld:** Attendance corrected, incorrect reporter loses 25pts
   - **Challenge dismissed:** No change, false challenger loses 25pts
   - **No challenges:** After 24 hours, attendance locked and final
   - All outcomes announced in messenger

**Accountability Mechanics:**
- **Reporter is public:** Name shown with submission (social accountability)
- **Penalty for errors:** -25pts for incorrect reporting
- **Penalty for false challenges:** -25pts for unsuccessful challenges
- **Vote ties:** Status quo wins (challenger loses)
- **Social dynamics:** Friend groups self-regulate through shared knowledge

**Edge Cases:**
- **No submission after 12 hours:** Reminder posted to group
- **No submission after 24 hours:** Event creator notified directly
- **No submission after 48 hours:** Event auto-expires, no bonuses awarded
- **Multiple simultaneous submissions:** First timestamp wins, others notified
- **All attendees agree in vote:** Fast resolution without waiting for all votes

**Integration with Other Features:**
- **Decay:** Attending event counts as "activity" (resets decay timer)
- **Seasons:** Event attendance tracked in season leaderboard as separate category
- **Weekly Bonus:** Event attendance counts toward weekly participation
- **Transactions:** Event bonuses create `event_attendance_bonus` transaction type

**User Stories:**
```
AS A group admin
WHEN I create an event
THEN I can set date, location, and attendance bonus

AS any group member
WHEN an event ends
THEN I can access the attendance form URL and record who attended

AS an attendee
WHEN attendance is recorded incorrectly
THEN I can challenge it within 24 hours via web form

AS a recorded attendee
WHEN a challenge is raised
THEN I vote via web link to help establish truth

AS the group
WHEN someone repeatedly submits incorrectly
THEN social pressure and point penalties naturally self-regulate
```

### Wager Rules
1. **Minimum Participants:** Creator + 1 other (2 total)
2. **No Editing:** Once first person joins, wager is locked
3. **Deadline Required:** Must be future timestamp
4. **Question Length:** 10-500 characters
5. **Multiple Choice Options:** 2-10 unique options
6. **Numeric Precision:** Up to 2 decimal places
7. **One Entry Per User:** Cannot join same wager twice

### Settlement Rules
1. **Creator Settles:** Original creator has first right to settle
2. **Settlement Window:** Creator has 48hrs after deadline before others can flag
3. **Dispute Window:** 24hrs after settlement to initiate dispute
4. **Dispute Threshold:** ≥30% of participants must dispute to trigger vote
5. **Vote Requirement:** >50% must vote to overturn
6. **Tie Handling:** Numeric ties split winnings equally

### Time Constraints
1. **Token Expiry:** One-time tokens expire after 24 hours
2. **Betting Window:** Closes at deadline (or earlier if specified)
3. **Dispute Voting:** 48hrs to complete vote
4. **Weekly Bonus:** Calculated Sunday 11:59 PM, granted Monday 12:00 AM

---

## User Experience Guidelines

### Messaging Platform (Telegram)
**Do:**
- Keep messages concise and scannable
- Use emojis sparingly for visual hierarchy
- Update messages inline when state changes (participant count)
- Respond immediately to button clicks with feedback
- Send private confirmations for joins/settlements

**Don't:**
- Spam the group with every action
- Use complex formatting (works differently per platform)
- Require multiple steps for simple actions
- Send walls of text

### Web Interface
**Do:**
- Show loading states for all async actions
- Provide clear validation feedback inline
- Preview how wager will look in messenger
- Allow keyboard navigation (accessibility)
- Mobile-responsive design (many users on mobile)
- Auto-save form drafts (localStorage)

**Don't:**
- Require multiple page loads for one action
- Hide critical info below the fold
- Use jargon or technical terms
- Force landscape orientation

---

## Success Metrics (Post-Launch)

### Engagement
- Daily Active Users (DAU) per group
- Wagers created per week per group
- Average participants per wager
- Participation rate (% of group members joining wagers)

### Health
- Average time to settlement (target: <24hrs after deadline)
- Dispute rate (target: <5% of wagers)
- Dispute overturn rate (indicates creator fairness)
- Token abandonment rate (created but not used)

### Growth
- New groups per week
- User retention (weekly cohorts)
- Wager completion rate (settled vs. abandoned)

---

## Risks & Mitigations

### Technical Risks
| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Telegram rate limits | High | Medium | Implement queue with backoff, batch notifications |
| Token collision/reuse | High | Low | UUID tokens, single-use validation, expiry |
| Race conditions (joining) | Medium | Medium | Database transactions, optimistic locking |
| Point calculation bugs | High | Medium | Extensive unit tests, dry-run settlement preview |

### Product Risks
| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Low adoption | High | Medium | Start with friend groups, iterate on feedback |
| Abuse/cheating | Medium | Medium | Dispute mechanism, admin oversight, social accountability |
| Settlement disputes | Medium | High | Clear rules, transparent voting, audit trail |
| Complexity creep | Medium | High | Strict MVP scope, aggressive feature deferral to Phase 2+ |
| Season prize disputes | Medium | Low | Clear disclaimers, platform uninvolved in money, group self-manages |
| Decay feels punishing | Low | Low | 2-week grace period, small penalty, clear warnings |
| Event attendance disputes | Low | Medium | 24hr challenge window, voting mechanism, social pressure |

### Operational Risks
| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Hosting costs | Low | Low | Start small, scale with usage |
| Support burden | Medium | Medium | Self-service help docs, clear error messages |
| Data loss | High | Low | Automated backups, point transaction audit trail |

---

## Development Phases

### Phase 1: Core MVP (8-10 weeks)
**Goal:** Launch a functional wagering platform with essential features for a single friend group to use end-to-end.

**Scope:**
- Binary wagers only (yes/no questions)
- Basic point economy (starting balance, wager join/win/loss)
- Web-based wager creation and settlement
- Telegram bot integration (announcements, join buttons, notifications)
- Simple dispute mechanism (flag only, no voting yet)
- User authentication (Telegram OAuth)
- Basic web dashboard (balance, active wagers, history)

**Week 1-2: Foundation**
- Project setup (Laravel, Vue, Inertia, Tailwind)
- Database schema + migrations (core tables only: users, groups, user_group, wagers, wager_entries, transactions)
- Authentication system (Telegram OAuth)
- Basic service layer (WagerService, PointService, TokenService)

**Week 3-4: Wager Core**
- Wager creation flow (web UI with token auth)
- Binary wager support only
- Telegram webhook handling
- Bot commands (/newbet, /mybets, /balance)
- Joining wagers via inline keyboards (Telegram)
- Point reservation and validation

**Week 5-6: Settlement & Disputes**
- Settlement web UI (one-time token flow)
- Point distribution logic
- Basic dispute flag (no voting, admin resolves manually)
- Transaction audit trail
- Web dashboard (view wagers, balance, history)

**Week 7-8: Polish & Testing**
- Error handling and validation
- Edge case handling
- Messenger interface improvements
- Basic templates (3-5 starter templates for binary wagers)
- Unit and integration tests
- Documentation

**Week 9-10: Soft Launch**
- Security audit
- Performance testing
- Deploy to production
- Onboard 1-2 test friend groups
- Monitor and gather feedback

**Deliverable:** Working platform where friends can create binary wagers, join via Telegram, settle via web, and track points.

---

### Phase 2: Enhanced Features (6-8 weeks)
**Goal:** Add engagement features and expand wager types based on Phase 1 learnings.

**Scope:**
- Multiple wager types (numeric, multiple choice)
- Point decay system
- Group seasons (basic version)
- Group events (manual attendance only)
- Dispute voting mechanism
- Weekly participation bonuses
- Enhanced templates
- Improved web dashboard

**Week 1-2: Wager Types**
- Numeric wagers (closest guess wins)
- Multiple choice wagers
- Updated settlement logic for new types
- Template expansion

**Week 3-4: Engagement Systems**
- Point decay implementation (scheduled job)
- Weekly bonus system (scheduled job)
- Warning notifications for decay
- Activity tracking

**Week 5-6: Seasons & Events**
- Season CRUD (web UI)
- Leaderboard calculation and display
- Event CRUD (web UI)
- Manual attendance recording
- Event bonus distribution

**Week 7-8: Dispute Voting**
- Voting UI for disputes
- Vote counting and resolution
- Automated dispute resolution
- Polish and testing

**Deliverable:** Full-featured platform with engagement loops, multiple wager types, and social event tracking.

---

### Phase 3: Future Exploration (Timeline TBD)
**Goal:** Expand platform capabilities based on actual usage patterns and user feedback.

**Approach:** Agile iteration - features listed here may be replaced with better ideas from real-world usage.

**Potential Features:**
- Additional messenger platforms (Slack, Discord)
- Advanced season features
- Event attendance streaks
- Revenge wagers
- Group challenges
- Wager analytics
- Admin panel enhancements
- Mobile app (if needed)

**Decision Point:** After Phase 2 completion, evaluate:
1. What features did users actually request?
2. What problems emerged that we didn't anticipate?
3. What's the adoption and retention looking like?
4. Should we double down on current platform or pivot?

**Philosophy:** Don't commit to Phase 3 scope until Phase 2 is live and we have real usage data.

---

## Open Questions & Decisions

### Resolved

1. **Point Deflation:** Should we implement season resets to prevent point accumulation?
   - **Decision:** Group-level seasons with optional point resets. Groups decide their own cadence.

2. **Group Privacy:** Should wagers/points be visible across groups if same user?
   - **Decision:** No. Complete isolation per group. Each group is independent economy.

3. **Creator Abandonment:** What if creator never settles?
   - **Decision:** Phase 1 - manual admin intervention. Phase 2 - any participant can flag after 48hrs, force settlement.

4. **Numeric Ties:** Should we allow "within X%" wins instead of exact?
   - **Decision:** Phase 1 - closest/ties split evenly. Phase 2 - explore range-based options.

5. **Template Contributions:** Allow users to submit custom templates?
   - **Decision:** No for MVP. Admin-curated only. May revisit in Phase 3 based on demand.

6. **Event Check-in Method:** QR codes vs manual confirmation?
   - **Decision:** Web-based manual confirmation with community recording. No QR codes. Trust-based system with challenge mechanism.

7. **Event Attendance Penalties:** Should no-shows face point penalties?
   - **Decision:** No penalties for no-shows. Only penalties for incorrect reporting (-25pts) or false challenges (-25pts).

8. **Engagement Features Priority:** Decay, streaks, challenges - which first?
   - **Decision:** Point decay in Phase 2 (high impact, low complexity). Streaks and challenges deferred to Phase 3.

### Deferred to Post-Launch

9. **Multi-platform Support:** When to add Slack, Discord, etc.?
   - **Decision:** Defer until after Phase 2. Validate architecture works well for Telegram first. Platform abstraction layer already in place for future expansion.

10. **Admin Panel Scope:** How much admin control needed?
    - **Decision:** Phase 1 - basic manual intervention. Phase 2 - structured admin panel. Full scope determined by actual support needs.

11. **Notification Preferences:** Allow users to customize notification frequency?
    - **Decision:** Defer to Phase 3. Start with opinionated defaults, adjust based on feedback.

12. **Wager Privacy:** Should groups have private wagers (not announced)?
    - **Decision:** Interesting idea. Defer to Phase 3 to evaluate use cases and demand.

---

## Appendix

### Example Wager Templates

```json
[
  {
    "name": "Sports Match",
    "type": "binary",
    "question_template": "Will {team_a} beat {team_b} on {date}?",
    "placeholders": ["team_a", "team_b", "date"],
    "default_stake": 100
  },
  {
    "name": "Numeric Prediction",
    "type": "numeric",
    "question_template": "How many {unit} will {subject} achieve by {deadline}?",
    "placeholders": ["unit", "subject", "deadline"],
    "default_stake": 100
  },
  {
    "name": "Yes/No Question",
    "type": "binary",
    "question_template": "Will {event} happen by {deadline}?",
    "placeholders": ["event", "deadline"],
    "default_stake": 50
  },
  {
    "name": "Multiple Choice",
    "type": "multiple_choice",
    "question_template": "Who will {achievement}?",
    "placeholders": ["achievement"],
    "default_stake": 100,
    "default_options": ["Option A", "Option B", "Option C"]
  }
]
```

### Telegram Bot Commands

```
/newbet - Create a new wager
/mybets - Show your active wagers
/balance - Check your point balance
/leaderboard - Group leaderboard (future)
/help - Show help message
/status - Check bot status
```

### Sample Point Transaction Flow

```
User A creates wager (stake: 100)
→ Transaction: -100 (reserved)
→ Balance: 900 (available)

User B joins (stake: 100)
→ Transaction: -100 (reserved)
→ Balance: 900 (available)

User C joins (stake: 100)
→ Transaction: -100 (reserved)
→ Balance: 900 (available)

Creator settles: User A wins
→ Pool: 300 points
→ User A: +300
→ Transaction: +300 (wager_win)
→ Final Balance: 1200

User B: +0 (wager_loss recorded)
→ Final Balance: 900

User C: +0 (wager_loss recorded)
→ Final Balance: 900
```

---

## Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-10-13 | Initial | Complete PRD based on product discussions |

---

**Next Steps:**
1. Review PRD with stakeholders
2. Technical architecture validation
3. Set up development environment
4. Create initial Laravel project scaffold
5. Begin Phase 1, Week 1 tasks