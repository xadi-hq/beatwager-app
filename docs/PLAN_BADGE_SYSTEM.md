# Badge System Implementation Plan

## Overview
A comprehensive badge/achievement system for BeatWager that rewards user engagement across wagers, events, challenges, and disputes. Badges can be earned, displayed, and (in some cases) revoked when underlying achievements are reversed.

---

## Phase 1: Foundation (Database & Models)

### 1.1 Create Migrations

**badges table:**
```
- id (uuid, primary)
- slug (string, unique) - e.g., 'wager_won_5', 'first_event_attended'
- name (string) - Display name, e.g., 'Winning Streak'
- description (string) - e.g., 'Won 5 wagers'
- category (enum) - 'wagers', 'events', 'challenges', 'disputes', 'special'
- tier (enum) - 'standard', 'bronze', 'silver', 'gold', 'platinum'
- is_shame (boolean, default false) - For negative achievements
- criteria_type (enum) - 'first', 'count', 'streak', 'comparative'
- criteria_event (string) - Event that triggers check, e.g., 'wager_won'
- criteria_threshold (int, nullable) - Count needed (null for 'first' type)
- criteria_config (json, nullable) - Additional criteria params
- image_slug (string) - Maps to image file, e.g., 'wager-won-5'
- sort_order (int) - For display ordering within category
- is_active (boolean, default true)
- created_at, updated_at
```

**user_badges table:**
```
- id (uuid, primary)
- user_id (uuid, fk users)
- badge_id (uuid, fk badges)
- group_id (uuid, nullable, fk groups) - null = global, set = group-specific
- awarded_at (timestamp)
- revoked_at (timestamp, nullable) - Set when badge is revoked
- revocation_reason (string, nullable) - Why it was revoked
- metadata (json, nullable) - Context: triggering wager_id, streak details, etc.
- notified_at (timestamp, nullable) - When notification was sent
- UNIQUE(user_id, badge_id, group_id) where revoked_at IS NULL
```

### 1.2 Create Enums

**BadgeCategory enum:**
- wagers, events, challenges, disputes, special

**BadgeTier enum:**
- standard, bronze, silver, gold, platinum

**BadgeCriteriaType enum:**
- first, count, streak, comparative

### 1.3 Create Models

**Badge model:**
- Relationships: userBadges(), users() (through user_badges)
- Scopes: active(), byCategory(), byTier()
- Accessors: imageUrl(), largeImageUrl(), smallImageUrl()

**UserBadge model:**
- Relationships: user(), badge(), group()
- Scopes: active() (where revoked_at IS NULL), revoked()
- Methods: revoke(reason), isActive()

### 1.4 Create Badge Seeder

Seed all system badges from README_BADGES_GUIDE.md:
- Events: first_event_created, first_event_attended, event_streak_5/10/20, first_no_show, no_show_5
- Challenges: first_challenge_requested, first_challenge_given, super_challenge, elimination_tap_out, elimination_winner
- Wagers: first_wager_created, first_wager_won, wager_lost_3, wager_won_5/10/20, most_wagers_settled
- Disputes: fraudster, judge_1, judge_5/10

---

## Phase 2: Badge Service & Criteria Checking

### 2.1 Create BadgeService

```php
class BadgeService
{
    // Main entry point - called after relevant events
    public function checkAndAward(User $user, string $criteriaEvent, ?Group $group = null, array $context = []): array;

    // Check specific badge criteria
    public function checkCriteria(Badge $badge, User $user, ?Group $group): bool;

    // Award a badge
    public function award(User $user, Badge $badge, ?Group $group = null, array $metadata = []): UserBadge;

    // Revoke a badge (for dispute reversals)
    public function revoke(UserBadge $userBadge, string $reason): void;

    // Re-check and potentially revoke badges after stat changes
    public function recheckAfterReversal(User $user, string $criteriaEvent, ?Group $group = null): array;

    // Get user's stats for criteria checking
    public function getUserStats(User $user, string $statType, ?Group $group = null): int;
}
```

### 2.2 Stat Tracking Methods

Methods to count user achievements (can be added to User model or separate service):
```php
// Wager stats
- wagersCreatedCount(?Group $group): int
- wagersWonCount(?Group $group): int
- wagersLostCount(?Group $group): int
- wagersSettledCount(?Group $group): int

// Event stats
- eventsCreatedCount(?Group $group): int
- eventsAttendedCount(?Group $group): int
- eventsMissedCount(?Group $group): int
- currentEventStreak(?Group $group): int

// Challenge stats
- challengesRequestedCount(?Group $group): int
- challengesGivenCount(?Group $group): int
- eliminationWinsCount(?Group $group): int

// Dispute stats
- disputesJudgedCount(?Group $group): int
- fraudPenaltiesCount(?Group $group): int
```

### 2.3 Criteria Type Handlers

```php
// First occurrence - check if count >= 1 and badge not already earned
private function checkFirstCriteria(Badge $badge, User $user, ?Group $group): bool;

// Count threshold - check if stat >= threshold
private function checkCountCriteria(Badge $badge, User $user, ?Group $group): bool;

// Streak - check current consecutive streak >= threshold
private function checkStreakCriteria(Badge $badge, User $user, ?Group $group): bool;

// Comparative - check if user is #1 in stat (e.g., most settlements)
private function checkComparativeCriteria(Badge $badge, User $user, ?Group $group): bool;
```

---

## Phase 3: Event Integration

### 3.1 Create BadgeAwarded Event

```php
class BadgeAwarded
{
    public function __construct(
        public UserBadge $userBadge,
        public User $user,
        public Badge $badge,
        public ?Group $group,
    ) {}
}
```

### 3.2 Create BadgeRevoked Event

```php
class BadgeRevoked
{
    public function __construct(
        public UserBadge $userBadge,
        public User $user,
        public Badge $badge,
        public string $reason,
    ) {}
}
```

### 3.3 Hook into Existing Events

**Trigger badge checks on:**
| Existing Event | Badge Criteria Event | Notes |
|----------------|---------------------|-------|
| WagerSettled | wager_won, wager_lost, wager_settled | Check winner/loser |
| WagerCreated | wager_created | |
| EventCreated | event_created | |
| EventAttendanceRecorded | event_attended, event_missed | Based on attendance status |
| ChallengeAccepted | challenge_requested | For the requester |
| ChallengeCompleted | challenge_given | For the challenger |
| EliminationResolved | elimination_winner, elimination_tap_out | |
| DisputeVoteCast | dispute_judged | |
| DisputeResolved (fraud) | fraudster | For the penalized user |

**Trigger badge re-checks (potential revocation) on:**
| Event | Re-check Criteria | Notes |
|-------|------------------|-------|
| DisputeResolved (reset) | wager_won, wager_lost | When wager results reversed |
| AdminAdjustment | Various | If admin manually adjusts stats |

### 3.4 Create Event Listeners

```php
// Award listener
class CheckBadgesOnEvent
{
    public function handle($event): void
    {
        $badgeService = app(BadgeService::class);
        $badgeService->checkAndAward(
            $event->user,
            $event->badgeCriteriaEvent,
            $event->group ?? null,
            $event->getContext()
        );
    }
}

// Revocation listener
class RecheckBadgesOnReversal
{
    public function handle(DisputeResolved $event): void
    {
        if ($event->resolution->requiresReversal()) {
            $badgeService = app(BadgeService::class);
            // Re-check affected users
            foreach ($event->affectedUsers as $user) {
                $badgeService->recheckAfterReversal($user, 'wager_won', $event->group);
                $badgeService->recheckAfterReversal($user, 'wager_lost', $event->group);
            }
        }
    }
}
```

---

## Phase 4: Notifications

### 4.1 Create SendBadgeNotification Listener

```php
class SendBadgeNotification
{
    public function handle(BadgeAwarded $event): void
    {
        $user = $event->user;
        $badge = $event->badge;
        $group = $event->group;

        // Send to group chat (if group-specific or user's primary group)
        if ($group) {
            $this->sendGroupNotification($user, $badge, $group);
        }

        // Send DM to user
        $this->sendDirectNotification($user, $badge);

        // Mark as notified
        $event->userBadge->update(['notified_at' => now()]);
    }

    private function sendGroupNotification(User $user, Badge $badge, Group $group): void
    {
        // Use sendPhoto with badge image + caption
        // Caption: "🎖️ {user} just earned the {badge.name} badge! {badge.description}"
        // Buttons: View Profile, All Badges
    }
}
```

### 4.2 Add sendPhoto to TelegramAdapter

Extend TelegramAdapter with photo sending capability:
```php
public function sendPhoto(
    string $chatId,
    string $photoPath, // Local file or URL
    ?string $caption = null,
    ?string $parseMode = 'HTML',
    ?array $buttons = null
): void;
```

### 4.3 Badge Image Serving

Option A: Public storage route
```php
// Route: GET /badges/{slug}.png
// Controller serves from storage/badges/{slug}.png
Route::get('/badges/{slug}.png', [BadgeController::class, 'image']);
```

Option B: Move badges to public storage
```bash
# Move to storage/app/public/badges/
# Access via /storage/badges/{slug}.png after php artisan storage:link
```

---

## Phase 5: Display & UI

### 5.1 Badge Display Components

**BadgeIcon.vue** - Single badge display
```vue
<template>
  <div class="badge-icon" :class="[sizeClass, tierClass]">
    <img :src="badge.imageUrl" :alt="badge.name" />
    <div v-if="showName" class="badge-name">{{ badge.name }}</div>
  </div>
</template>
```

**BadgeShowcase.vue** - User's badge collection
```vue
<template>
  <div class="badge-showcase">
    <div v-for="category in categories" :key="category">
      <h3>{{ categoryName(category) }}</h3>
      <div class="badge-grid">
        <BadgeIcon
          v-for="badge in badgesByCategory(category)"
          :key="badge.id"
          :badge="badge"
          :earned="isEarned(badge)"
        />
      </div>
    </div>
  </div>
</template>
```

### 5.2 Add Badges to Dashboard

- Add "Badges" tab to Dashboard/Me.vue
- Show recently earned badges
- Link to full badge showcase

### 5.3 Add Badges to User Profile

- Show top badges on profile header
- "View all badges" link to showcase

### 5.4 Badge Detail Modal

- Badge image (large)
- Name, description
- When earned (if earned)
- Progress toward earning (for tiered badges)

---

## Phase 6: Revocation Handling

### 6.1 Revocation Logic

When a dispute reverses wager results:
1. Identify affected users (previous winner becomes loser)
2. For each affected user, re-check relevant badge criteria
3. If user no longer qualifies (e.g., now has 4 wins instead of 5):
   - Set revoked_at and revocation_reason on user_badge
   - Dispatch BadgeRevoked event
4. Optionally: Award "Badge Revoked" shame badge after X revocations?

### 6.2 Revocation Notification

- Notify user (DM only, not group) that badge was revoked
- Explain reason: "Your 'Winning Streak' badge was revoked because a disputed wager was reversed."

### 6.3 Revoked Badge Display

- Show revoked badges grayed out with "Revoked" label?
- Or completely hide revoked badges?
- Decision: **Hide revoked badges** (cleaner UX, user_badge record preserved for audit)

---

## Phase 7: Future Enhancements

### 7.1 Group-Custom Badges
- Groups can define custom badges with custom images
- Group admins upload badge image
- Define custom criteria (count-based only for simplicity)

### 7.2 Badge Sharing
- Share badge achievement to social media
- "I just earned X badge on BeatWager!"

### 7.3 Badge Leaderboards
- Most badges earned
- Rarest badges
- First to earn new badge

### 7.4 Seasonal Badges
- Time-limited badges for special events
- "2024 New Year" badge, etc.

---

## Implementation Checklist

### Phase 1: Foundation
- [ ] Create BadgeCategory enum
- [ ] Create BadgeTier enum
- [ ] Create BadgeCriteriaType enum
- [ ] Create badges migration
- [ ] Create user_badges migration
- [ ] Create Badge model
- [ ] Create UserBadge model
- [ ] Create BadgeSeeder with all system badges
- [ ] Run migrations and seeder

### Phase 2: Badge Service
- [ ] Create BadgeService class
- [ ] Implement getUserStats methods
- [ ] Implement checkCriteria for each criteria type
- [ ] Implement award method
- [ ] Implement revoke method
- [ ] Implement recheckAfterReversal method
- [ ] Write unit tests for BadgeService

### Phase 3: Event Integration
- [ ] Create BadgeAwarded event
- [ ] Create BadgeRevoked event
- [ ] Create CheckBadgesOnEvent listener
- [ ] Create RecheckBadgesOnReversal listener
- [ ] Hook listeners to existing events (WagerSettled, EventAttended, etc.)
- [ ] Test badge awarding on various actions

### Phase 4: Notifications
- [ ] Add sendPhoto method to TelegramAdapter
- [ ] Set up badge image serving (route or public storage)
- [ ] Create SendBadgeNotification listener
- [ ] Test notification with badge image in Telegram

### Phase 5: Display
- [ ] Create BadgeIcon.vue component
- [ ] Create BadgeShowcase.vue component
- [ ] Add Badges tab to Dashboard
- [ ] Add badges to user profile
- [ ] Create badge detail modal

### Phase 6: Revocation
- [ ] Implement revocation in DisputeResolved handler
- [ ] Test badge revocation scenario
- [ ] Create revocation notification

### Phase 7: Polish
- [ ] Delete test command (TestBadgeNotification)
- [ ] Add badge count to user stats
- [ ] Performance optimization (eager loading, caching)
- [ ] Documentation

---

## File Structure

```
app/
├── Enums/
│   ├── BadgeCategory.php
│   ├── BadgeTier.php
│   └── BadgeCriteriaType.php
├── Models/
│   ├── Badge.php
│   └── UserBadge.php
├── Services/
│   └── BadgeService.php
├── Events/
│   ├── BadgeAwarded.php
│   └── BadgeRevoked.php
├── Listeners/
│   ├── CheckBadgesOnEvent.php
│   ├── RecheckBadgesOnReversal.php
│   └── SendBadgeNotification.php
├── Http/Controllers/
│   └── BadgeController.php (for image serving, API)

database/
├── migrations/
│   ├── xxxx_create_badges_table.php
│   └── xxxx_create_user_badges_table.php
├── seeders/
│   └── BadgeSeeder.php

resources/js/
├── Components/
│   ├── BadgeIcon.vue
│   └── BadgeShowcase.vue
├── Pages/
│   └── Badge/
│       └── Show.vue (badge detail page)

storage/
└── badges/
    ├── wager-won-5.png
    ├── first-event-attended.png
    └── ... (all badge images)
```

---

## Notes

- Badge images should be 512x512 PNG with transparent background
- Consider adding 64x64 thumbnails for list views
- All badge checks should be wrapped in try-catch to prevent blocking core functionality
- Badge notifications are nice-to-have; if they fail, badge should still be awarded
- Group-specific badges (group_id set) are only visible/relevant within that group
