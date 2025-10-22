# BeatWager TODO

**Last Updated:** October 22, 2025
**Status:** Core features complete, LLM integration complete, Seasons implemented, Point reconciliation active, Event cancellation live, Prize system ready

## 🔥 HIGH PRIORITY: Ready to Implement

### 1. Message Tracking Integration (Phase 2)
**Status**: Infrastructure complete, needs integration
**Time**: 2-4 hours
**Dependency**: Existing `MessageTrackingService`

- ✅ `sent_messages` table exists
- ✅ `MessageTrackingService` with anti-spam rules
- ⏳ **TODO**: Integrate `canSendMessage()` into all messaging services:
  - Engagement prompts (SendEngagementPrompts)
  - Birthday reminders (SendBirthdayReminders - when created)
  - Weekly recaps (SendWeeklyRecap - when created)
  - Season milestone drops
  - Log all sends to `sent_messages`
- ⏳ **TODO**: Add LLM context using `getRecentHistory()`
  - "Remember the Marathon bet from last Tuesday?"

### 2. Notification Preferences System
**Status**: Partial - needs completion
**Time**: 3-5 hours

**What exists:**
- ✅ `messages:send-scheduled` command (daily 8am)
- ✅ Birthday day-of messages working
- ✅ Point decay warnings active

**TODO:**
- ⏳ Create `SendBirthdayReminders` job (-7 days advance warning)
  - Check `notification_preferences.birthday_reminders`
  - "When are WE celebrating your upcoming 40th birthday John?"

### 3. Prize System LLM Integration
**Status**: Backend complete, needs LLM message integration
**Time**: 1-2 hours
**Completed**: October 22, 2025

**What's done:**
- ✅ `prize_structure` JSON column added to `group_seasons` [GroupSeason.php:25](../app/Models/GroupSeason.php#L25)
- ✅ Prize Configuration UI in season settings [SeasonManagement.vue:224](../resources/js/Components/SeasonManagement.vue#L224)
- ✅ 8 prize positions: Winner, Runner-up, Loser, Most Active, Most Social, Most Servant, Most Generous, Most Improved
- ✅ Dynamic row management (add/remove prizes)
- ✅ Backend validation [SeasonController.php:82](../app/Http/Controllers/SeasonController.php#L82)
- ✅ Display prizes in active season card [SeasonManagement.vue:181](../resources/js/Components/SeasonManagement.vue#L181)

**TODO:**
- ⏳ Display prizes in season start LLM messages (MessageService integration)
- ⏳ Display prizes in season end LLM messages (MessageService integration)
- ⏳ Calculate and announce prize winners at season end

---

## 🔍 INVESTIGATE & CLARIFY

### Engagement Prompts Enhancement
**Current**: Basic system working (hourly stale wager checks)
**Question**: Are these future enhancements needed now?

- ✅ Current: LLM encouragement for 0-1 participant wagers after 24h
- ⏳ Future: Target specific users by activity patterns?
- ⏳ Future: Vary by wager characteristics (LLM can already do this)?

### Code Quality: Centralize Telegram Callbacks
**Status**: Optional refactor
**Time**: 2 hours
**Question**: Worth doing now for i18n prep?

- ⏳ Create `/lang/en/system.php` for callback strings
- ⏳ Move hardcoded `answerCallbackQuery()` from TelegramWebhookController
- **Benefits**: i18n ready, consistent errors

### Event Testing Coverage
**Current**: 40+ unit tests exist
**Question**: What integration/E2E gaps are critical?

**Exists:**
- ✅ EventService unit tests (RSVP, attendance, bonuses)
- ✅ Model tests (GroupEvent, GroupEventRsvp, GroupEventAttendance)

**Needs investigation:**
- ⏳ Integration tests: web → telegram notification flow?
- ⏳ E2E tests: full lifecycle (create → RSVP → attend → bonus)?

### No-Show Penalties
**Status**: Not started
**Time**: 6-8 hours
**Question**: Priority for v1 launch? Can handle manually in DB for now

- ⏳ RSVP "Going" but no check-in → configurable penalty
- ⏳ Consecutive misses → decay multiplier
- ⏳ Grace period/excuse mechanism

### Challenge/Dispute System
**Status**: Not started
**Time**: 8-10 hours
**Question**: Can handle manually for now?

- ⏳ Dispute attendance claims
- ⏳ Voting system for challenges
- ⏳ Auto-expiry for unrecorded events (48h)

### Event Attendance Streaks
**Status**: Not started
**Time**: 3-4 hours
**Question**: Nice-to-have or critical engagement feature?

- ⏳ Track consecutive attendance
- ⏳ Apply multiplier (e.g., 3rd consecutive = 1.2x bonus)
- ⏳ Display streaks in profile
- ⏳ Announce streaks in group

### Smart Notifications Audit
**Time**: 2 hours (audit only)
**Question**: What exists vs what's missing?

**Review what exists:**
- Engagement prompts (stale wagers)
- Decay warnings (day 12)
- Birthday messages
- Season milestone drops
- Event attendance prompts

**Identify gaps:**
- Post-loss encouragement?
- Pre-deadline urgency?
- Low balance warnings?
- Inactive friend nudges?

### Personal Analytics Modal
**Status**: Not started
**Time**: 4-5 hours
**Question**: Triggered from Win Rate card on Me.vue?

- ⏳ Win rate over time chart
- ⏳ Earnings history graph
- ⏳ Favorite wager types
- ⏳ Point balance history
- ⏳ Streaks and achievements

---

## 🔵 LOW PRIORITY: Future Enhancements

### Message Chunking & Delays
- ⏳ Add delay capability to MessengerAdapter
- ⏳ Message sequences with delays
- ⏳ LLM `[DELAY:10]` syntax support

### External APIs (GIFs & Jokes)
- ⏳ Giphy API integration
- ⏳ JokeAPI integration
- ⏳ Make available to LLM via function calling

### Badge System
- ⏳ `badges` table migration
- ⏳ BadgeService calculation logic
- ⏳ Types: oracle, degen, shark, loyalist, referee, ghost
- ⏳ Display in leaderboard and profiles

### Revenge Bet System
- ⏳ `OfferRevengeBet` job (losses >100pts)
- ⏳ "Rematch" quick action on settled wager page

### Long-tail Bet Reminders
- ⏳ `RemindLongWagers` job (wagers >30 days out)
- ⏳ Special UI treatment

### UI/UX Polish
- ⏳ Error message improvements
- ⏳ Loading states
- ⏳ Toast messages
- ⏳ Confirmation dialogs
- ⏳ Skeleton loaders
- ⏳ Mobile responsive improvements

### Edge Cases
- ⏳ User leaves group mid-wager
- ⏳ Network retry logic
- ⏳ LLM fallback to templates

---

## 🏗️ REFACTORING (When Needed)

### Platform-Agnostic Architecture
- ⏳ Move telegram columns to `messenger_services` table
- ⏳ Make groups platform agnostic
- ⏳ Implement `MessagingPlatform` interface

### PHP 8.3 Enums
- ⏳ Create: WagerType, WagerStatus, WagerEntryResult, Platform, TransactionType, UserRole
- ⏳ Update models to use Enums

---

## 🔒 SECURITY & PERFORMANCE (Pre-Launch)

### Security Audit
- ⏳ CSRF protection verification
- ⏳ SQL injection prevention
- ⏳ XSS protection
- ⏳ One-time token security review
- ⏳ Environment variable handling
- ⏳ LLM API key encryption

### Performance Optimization
- ⏳ N+1 query prevention
- ⏳ Redis caching
- ⏳ Queue non-critical notifications
- ⏳ Asset optimization
- ⏳ CDN setup
- ⏳ LLM response caching

### Monitoring & Logging
- ⏳ Error tracking (Sentry)
- ⏳ Performance monitoring
- ⏳ Webhook failure alerts
- ⏳ Database monitoring
- ⏳ Queue job monitoring
- ⏳ LLM usage/cost monitoring

---

## 🚀 DEPLOYMENT (When Ready)

### CI/CD Pipeline
- ⏳ GitHub Actions (tests, linting, type checking)
- ⏳ Automated deployment
- ⏳ Database migrations
- ⏳ Rollback mechanism
- ⏳ Zero-downtime strategy

### Production Setup
- ⏳ Server provisioning (VPS/cloud)
- ⏳ Managed PostgreSQL
- ⏳ Redis configuration
- ⏳ Queue workers (Supervisor)
- ⏳ SSL certificate
- ⏳ Domain + load balancer

### Soft Launch
- ⏳ Onboard 1-2 test groups
- ⏳ Monitor for 2 weeks
- ⏳ Gather feedback
- ⏳ Fix critical bugs
- ⏳ Iterate on UX

---

## 📦 FEATURE BACKLOG (Phase 2+)

### Wager Types
- ⏳ Numeric wagers (closest guess wins)
- ⏳ String wagers (open-ended text)
- ⏳ Wager templates
- ⏳ Event-specific wagers

### Engagement
- ⏳ Wager participation streaks
- ⏳ Group challenges
- ⏳ Monthly participation goals

### Advanced (Phase 3)
- ⏳ Multi-platform (Slack, Discord, WhatsApp)
- ⏳ Group analytics dashboard
- ⏳ Cross-group seasons
- ⏳ User-generated templates
- ⏳ Mobile native apps
- ⏳ Automated outcome detection (sports APIs)
- ⏳ Voice/video LLM integration

---

## 📝 NOTES

- Run tests before commits: `make test`
- Point reconciliation: Review logs after 2-4 weeks (mid-November 2025)
- See [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md) for patterns
- See [point-reconciliation.md](./point-reconciliation.md) for monitoring details
- Code quality: 4 of 5 phases complete (80%) - tracked in `./claudedocs/`

---

## 📊 SESSION SUMMARY - October 22, 2025

**Work Session Duration**: ~5.5 hours
**Features Completed**: 3 major systems
**Lines of Code Added**: ~800+
**Migrations Created**: 2
**Files Modified**: 15+

### Completed Today

1. **Point Reconciliation System** (1.5h)
   - Weekly monitoring command with dry-run, fix, and threshold modes
   - Zero discrepancies found in initial run across 35 groups
   - Comprehensive logging and alerting system

2. **Event Cancellation** (1.5h)
   - Full-stack implementation (DB → Service → Controller → UI)
   - Creator-only with validation (upcoming events, not started)
   - Confirmation modal + toast notifications
   - Mobile and desktop responsive

3. **Prize System** (2.5h)
   - Database schema with 8 prize position types
   - Dynamic UI for prize configuration (add/remove rows)
   - Backend validation and storage
   - Display in active season cards
   - Ready for LLM message integration

### Testing Commands

```bash
# Point Reconciliation
docker exec beatwager-app php artisan points:reconcile
docker exec beatwager-app php artisan points:reconcile --dry-run
docker exec beatwager-app php artisan points:reconcile --threshold=50

# Build Assets
npm run build

# Run Tests
docker exec beatwager-app php artisan test
```

### Next Session Priorities

1. Prize System LLM Integration (1-2h) - Display prizes in season messages
2. Message Tracking Integration (2-4h) - Wire up canSendMessage() checks
3. Birthday -7 Day Reminder (1-2h) - Advance birthday notifications
