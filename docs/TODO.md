# BeatWager TODO

**Last Updated:** October 16, 2025
**Current Phase:** Phase 2 - LLM Integration & Engagement Features
**Status:** Core features complete, Group customization complete, LLM service implemented, Integration & Engagement features next

---

## ✅ RECENTLY COMPLETED (October 2025)

### LLM Integration Complete (October 16, 2025 - Today's Session)
- ✅ **Complete Event/Listener Architecture for All Messages**
  - Created `WagerSettled` event + `SendWagerSettlement` listener
  - Created `EventCreated` event + `SendEventAnnouncement` listener
  - Created `AttendanceRecorded` event + `SendAttendanceAnnouncement` listener
  - Created `WagerJoined` event + `SendWagerJoinAnnouncement` listener
  - All listeners queued with 3 retries and 5s backoff
  - Replaced ALL hardcoded Telegram message methods with LLM-powered system

- ✅ **Message Types & LLM Integration**
  - Added `event.announced` and `event.attendance_recorded` to messages.php
  - Updated `wager.joined` for engagement announcements with FOMO tone
  - Added `eventAnnouncement()`, `attendanceRecorded()`, and `wagerJoined()` methods to MessageService
  - All messages now use group's `points_currency_name` (e.g., "chips" not "points")
  - All messages apply bot personality from `bot_tone` setting
  - Dynamic word limits per message type (25-200 words)
  - Emoji guidance based on message length

- ✅ **Engagement Trigger System**
  - Created `EngagementTriggerService` to detect contextual triggers when users join wagers
  - Implemented 11 Phase 1 triggers across 5 categories:
    - Position: is_first, is_leader, is_underdog
    - Stakes: is_high_stakes, stake_percentage
    - Comeback: is_comeback, days_inactive
    - Momentum: is_contrarian, is_bandwagon
    - Timing: is_last_minute, is_early_bird
  - Enhanced LLM prompts with `buildTriggersGuidance()` method
  - Triggers provide contextual hints like "trendsetter!", "look who's back!", "bold contrarian move!"
  - Creates FOMO and personality in join announcements
  - Documented 15 total triggers in `/docs/ENGAGEMENT_TRIGGERS.md` (Phase 2-3 ready for future)

- ✅ **LLM Metrics & Monitoring**
  - Created `llm_usage_daily` table for aggregated metrics
  - Created `AggregateLLMMetrics` command (scheduled daily at midnight)
  - Metrics tracked: total calls, cache hits, fallbacks, cost, providers, message types
  - LLM Usage tab in group settings shows monthly metrics dashboard
  - Cache hit rate calculation and cost tracking

- ✅ **Form Validation Improvements**
  - Created reusable `FormError` component for inline field errors
  - Created `FormErrorBox` component for general form errors
  - Applied to all forms: Wager Create, Event Create, Wager Settlement
  - Consistent error display across entire application
  - Fixed settlement bug (sending telegram_id instead of user UUID)

- ✅ **Removed All Hardcoded Messages**
  - Deleted `postSettlementToTelegram()` from WagerController (35+ lines)
  - Deleted `postEventToTelegram()` from EventController (55+ lines)
  - Deleted `announceAttendanceToTelegram()` from EventController (40+ lines)
  - All replaced with simple event dispatches (1 line each)
  - 130+ lines of hardcoded message logic removed

### Infrastructure - Operational Logging
- ✅ Added operational channel to `config/logging.php`
  - JSON-formatted logs for structured parsing
  - 14-day retention policy
  - Separate from application logs
- ✅ Created `LogService` for operational logging
  - LLM event tracking (generation, caching, errors)
  - Performance metrics logging
  - Feature usage tracking
  - Error logging with context
- ✅ Created `audit_events` table for LLM context storage
  - Stores event summaries for grudge memory
  - Participant tracking
  - Impact/results metadata
  - Queryable for AI-powered features

### Group Customization Features (October 16, 2025)
- ✅ **Custom Point Currency Names**
  - Added `points_currency_name` to groups table (default: "points")
  - Updated all messaging to use custom currency
  - UI displays custom currency everywhere (dashboards, wagers, events, transactions)
  - Group settings drawer for configuration

- ✅ **Bot Notification Preferences**
  - Added `notification_preferences` JSON field to groups table
    - Birthday reminders (boolean, default: false)
    - Event reminders (boolean, default: true)
    - Wager reminders (boolean, default: true)
    - Weekly summaries (boolean, default: false)
  - Group settings UI with toggles
  - Ready for notification jobs to respect preferences

- ✅ **AI-Powered Bot Personality (Database & UI)**
  - Added `llm_api_key` field (encrypted, nullable)
  - Added `llm_provider` field (anthropic, openai)
  - Added `bot_tone` text field for personality instructions
  - Group settings UI with LLM configuration
    - API key input with encrypted storage
    - Tone textarea with examples
    - Provider selection

- ✅ **LLM Service Implementation**
  - Created `LLMService` with Anthropic & OpenAI support
  - Caching layer (1-hour TTL for similar prompts)
  - Fallback to default templates if LLM fails
  - Cost estimation and performance tracking
  - Integration with operational logging
  - Created `MessageContext` DTO for structured message data
  - Created `lang/en/messages.php` with LLM-ready message metadata
    - Intent descriptions for each message type
    - Required fields validation
    - Fallback templates
    - Tone hints for personality

- ✅ **Group Settings Drawer UI**
  - Reusable `Drawer` component (slide-over pattern)
  - `GroupSettingsForm` component with 3 tabs:
    - General (currency name, description)
    - Notifications (4 preference toggles)
    - Bot Personality (LLM key, provider, tone)
  - Accessible from group detail page
  - Settings button in navbar opens drawer
  - Auto-refreshes data after updates

- ✅ **Profile Settings Drawer UI**
  - Reusable drawer for user profile
  - Clickable username in navbar opens profile drawer
  - Profile tab removed from Dashboard (cleaner focus)
  - Consistent drawer pattern across app

---

## HIGH PRIORITY: LLM Integration & Engagement

### 1. Integrate LLM Service into Message Flow ✅ COMPLETED (October 16, 2025)
- ✅ Update `MessageService` to use `LLMService`
  - Check if group has LLM configured
  - If yes: generate personalized message via LLM
  - If no: use fallback template from `messages.php`
  - Handle LLM failures gracefully (fallback to templates)
- ✅ Apply to all bot message types:
  - ✅ Wager announcements (`wager.announced`) - Event/Listener pattern
  - ✅ Wager settlements (`wager.settled`) - Event/Listener pattern
  - ✅ Wager reminders (`wager.reminder`) - SendSettlementReminders command
  - ✅ Event announcements (`event.announced`) - Event/Listener pattern
  - ✅ Event attendance (`event.attendance_recorded`) - Event/Listener pattern
  - ✅ Decay warnings (`decay.warning`, `decay.applied`) - MessageService methods
  - ⏳ Badge announcements (when badge system implemented)
- ✅ Add LLM usage metrics to group stats
  - ✅ Track: total calls, cache hits, estimated cost, fallbacks, providers, message types
  - ✅ Display in group settings LLM Usage tab (read-only monthly stats)
  - ✅ Daily aggregation job (`llm:aggregate`) stores metrics in `llm_usage_daily` table
  - ✅ Metrics dashboard shows: Total Calls, Cost, Cache Hit Rate, Fallbacks

### 2. Badge System (US-009)
- ⏳ Create `badges` table migration
  - Fields: id, user_id, group_id, badge_type, earned_at, season_id
  - Badge types: 'oracle', 'degen', 'shark', 'loyalist', 'referee', 'ghost'
- ⏳ Create `Badge` model with relationships
- ⏳ Create `BadgeService` for calculation logic
  - `calculateBadges(Group $group)` - run weekly via scheduled job
  - Criteria for each badge type (see IMPLEMENTATION_PLAN.md)
- ⏳ Create `AssignBadges` scheduled job (weekly)
- ⏳ Add badge announcement messages (use LLM for personality)
- ⏳ Display badges in leaderboard and user profiles
- ⏳ Store badge events in `audit_events` for grudge context

### 3. Grudge Memory System (US-006)
- ⏳ Create `GrudgeService` for recent history
  - `getRecentHistory(User $user1, User $user2, Group $group): array`
  - Query last 5 wagers between two users
  - Format context for LLM ("Sarah has beaten John 3 times in a row")
- ⏳ Integrate grudge context into settlement messages
  - Pass grudge history to LLM via `MessageContext`
  - LLM generates dramatic commentary based on history
- ⏳ Store wager outcomes in `audit_events` for faster queries

### 4. Notification Jobs (Respect Preferences)
- ⏳ Create `SendBirthdayReminders` job (daily check)
  - Check `notification_preferences.birthday_reminders`
  - Send only to groups with preference enabled
- ⏳ Update decay warning job to respect preferences
  - Check `notification_preferences.wager_reminders`
- ⏳ Create `SendWeeklyRecap` job (weekly summary)
  - Check `notification_preferences.weekly_summaries`
  - Generate engaging summary with LLM if configured
  - Stats: most wins, biggest pot, most active member

### 5. Code Quality & Organization
- ⏳ **Centralize Telegram Callback Responses** (Optional improvement)
  - Create `/lang/en/system.php` for callback acknowledgments and error messages
  - Move hardcoded `answerCallbackQuery()` strings from TelegramWebhookController
  - Keep separate from `messages.php` (LLM messages) vs `system.php` (instant feedback)
  - Benefits: Easier i18n support, consistent error messages, centralized maintenance
  - Categories:
    - Wager responses: 'not_found', 'no_longer_open', 'deadline_passed', 'already_joined', 'placed_success'
    - Event responses: 'not_found', 'no_longer_active', 'rsvp_deadline_passed', 'invalid_format'
    - Error responses: 'insufficient_points', 'generic_error'
  - Note: These stay hardcoded (no LLM) - they're Telegram API requirements for instant feedback

---

## MEDIUM PRIORITY: Engagement Features

### 5. Revenge Bet System (US-012)
- ⏳ Create `OfferRevengeBet` job (triggered after big losses)
  - Detect losses >100 points
  - Create auto-generated revenge bet opportunity
  - Send targeted message to loser with LLM personality
- ⏳ Add "Rematch" quick action to settled wager page
  - One-click to create reverse wager
  - Same stakes, opposite outcome

### 6. Long-tail Bet Reminders (US-014)
- ⏳ Create `RemindLongWagers` job (weekly check for wagers >30 days out)
  - Send reminder about upcoming long-dated wagers
  - "Don't forget: Marathon bet settles in 45 days!"
- ⏳ Special UI treatment for long wagers (badge/indicator)

### 7. Bailout System (US-011)
- ⏳ Create `bailout_assignments` table migration
  - Fields: id, user_id, group_id, bailout_number, assignment_type, details, status, expires_at
  - Assignment types: 'task', 'challenge', 'trivia', 'dare'
- ⏳ Create `BailoutAssignment` model
- ⏳ Create `AssignBailout` service
  - Trigger when user balance drops below threshold
  - Generate random assignment based on group type
  - LLM generates creative assignment descriptions
- ⏳ Add bailout completion flow in web UI
  - Submit proof (text/photo)
  - Group votes to approve/reject
  - Points restored on approval

---

## Current Work: Phase 2 Final Polish

### Testing - Events System
- ⏳ EventService unit tests (RSVP, attendance, bonus distribution)
- ⏳ Integration tests for RSVP flow
- ⏳ Integration tests for attendance recording flow
- ⏳ E2E tests for complete event lifecycle

### Telegram Messaging - Events ✅ COMPLETED (October 16, 2025)
- ✅ Event announcement messages to group (when created)
  - ✅ Event/Listener architecture (EventCreated → SendEventAnnouncement)
  - ✅ Uses LLM for personality if configured with fallback templates
  - ✅ Includes RSVP inline buttons (Going/Maybe/Can't Make It)
  - ✅ Uses group's custom currency name
- ✅ RSVP inline buttons in Telegram (handled by TelegramWebhookController)
- ⏳ Attendance prompt messages (auto-sent after event via scheduled job)
- ✅ Attendance confirmation messages (when submitted)
  - ✅ Event/Listener architecture (AttendanceRecorded → SendAttendanceAnnouncement)
  - ✅ Uses LLM to celebrate attendees with bot personality
  - ✅ Lists all attendees with natural language formatting
  - ✅ Mentions point bonus with custom currency

### UI/UX Polish
- ⏳ Frontend error message improvements
- ⏳ Loading states on all async operations
- ⏳ Success/error toast messages
- ⏳ Confirmation dialogs for destructive actions
- ⏳ Skeleton loaders for data fetching

### Edge Cases
- ⏳ User leaves group mid-wager (need to handle)
- ⏳ Network errors during critical operations (retry logic needed)
- ⏳ LLM API failures (graceful fallback to templates)

### Mobile Responsive Design
- ⏳ Test all pages on mobile devices
- ⏳ Touch-friendly button sizes
- ⏳ Mobile navigation menu
- ⏳ Responsive table layouts

---

## Phase 2.1: Advanced Event Features (Future)

### No-Show Penalties (Optional per group)
- ⏳ RSVP "Going" but don't check in → configurable penalty
- ⏳ Miss consecutive events → decay multiplier
- ⏳ Grace period/excuse mechanism

### Challenge System
- ⏳ Dispute mechanism for attendance claims
- ⏳ Voting system for challenges
- ⏳ Penalties for incorrect reporting
- ⏳ Auto-expiry for unrecorded events (48 hours)

### Event Leaderboard
- ⏳ Track "most social" member metrics
- ⏳ Attendance percentage tracking
- ⏳ Longest streak display

---

## Refactoring & Architecture Improvements (MEDIUM PRIORITY)

### Platform-Agnostic Refactoring
- ⏳ Move telegram columns from users table to messenger_services table
  - Create `messenger_services` table (user_id, platform, platform_user_id, username, first_name, etc.)
  - Migrate existing telegram_* columns from users table
  - Update User model relationships
  - Update authentication flow to use messenger_services

- ⏳ Make groups platform agnostic
  - Replace telegram_* columns with generic platform columns
  - Add `platform` enum column (telegram, slack, discord)
  - Rename: `telegram_chat_id` → `platform_chat_id`
  - Rename: `telegram_chat_title` → `platform_chat_title`
  - Rename: `telegram_chat_type` → `platform_chat_type`

- ⏳ Implement `MessagingPlatform` interface (see IMPLEMENTATION_PLAN.md)
  - Create interface with sendMessage, editMessage, deleteMessage methods
  - Implement `TelegramPlatform` class
  - Refactor `TelegramWebhookController` to use abstraction
  - Future: `DiscordPlatform`, `SlackPlatform`, `WhatsAppPlatform`

### PHP 8.3 Enums
- ⏳ Create Enums in ./app/Enums/
  - `WagerType` enum (binary, multiple_choice, numeric, date)
  - `WagerStatus` enum (open, settled, disputed, cancelled)
  - `WagerEntryResult` enum (pending, won, lost, tied)
  - `Platform` enum (telegram, slack, discord)
  - `TransactionType` enum (wager_join, wager_win, wager_refund, decay, weekly_bonus, etc.)
  - `UserRole` enum (participant, creator, admin)

- ⏳ Update models to use Enums
  - Wager model: use WagerType, WagerStatus
  - WagerEntry model: use WagerEntryResult
  - Transaction model: use TransactionType
  - User-Group pivot: use UserRole

### Database Migration Cleanup
- ⏳ Consolidate Schema::table() migrations
  - Review all migrations in database/migrations/
  - Merge changes into original Schema::create() migrations
  - Test with `php artisan migrate:fresh`
  - Delete redundant Schema::table() migration files

---

## Week 9-10: Security, Performance & Soft Launch

### Security Audit
- ⏳ CSRF protection verification
- ⏳ SQL injection prevention check
- ⏳ XSS protection verification
- ⏳ One-time token validation security review
- ⏳ Rate limiting on API endpoints
- ⏳ Telegram webhook validation
- ⏳ Secure environment variable handling
- ⏳ LLM API key encryption validation

### Performance Optimization
- ⏳ Database query optimization (add indexes where needed)
- ⏳ N+1 query prevention (eager loading review)
- ⏳ Redis caching for frequent reads
- ⏳ Queue all non-critical notifications
- ⏳ Asset optimization (minification, compression)
- ⏳ CDN setup for static assets
- ⏳ LLM response caching optimization

### CI/CD Pipeline
- ⏳ GitHub Actions workflow for CI (tests, linting, type checking)
- ⏳ GitHub Actions workflow for deployment
- ⏳ Automated database migrations in deployment
- ⏳ Rollback mechanism for failed deployments
- ⏳ Zero-downtime deployment strategy

### Production Deployment
- ⏳ Set up production server (VPS or cloud)
- ⏳ Configure PostgreSQL (managed service recommended)
- ⏳ Configure Redis
- ⏳ Set up queue workers with Supervisor
- ⏳ SSL certificate setup
- ⏳ Domain configuration
- ⏳ Load balancer configuration

### Monitoring & Logging
- ⏳ Error tracking (Sentry or similar)
- ⏳ Performance monitoring (response times)
- ⏳ Telegram webhook failure alerts
- ⏳ Deployment status notifications
- ⏳ Database performance monitoring
- ⏳ Queue job monitoring
- ⏳ LLM usage and cost monitoring
- ⏳ Operational log analysis dashboard

### Soft Launch
- ⏳ Onboard 1-2 test friend groups
- ⏳ Monitor usage for first 2 weeks
- ⏳ Gather feedback via surveys/interviews
- ⏳ Fix critical bugs from feedback
- ⏳ Iterate on UX based on user behavior
- ⏳ Monitor LLM usage and costs

---

## Known Issues & Technical Debt

### High Priority
- ⏳ TelegramWebhookController not unit tested
  - Current: `new BotApi()` in constructor makes it unmockable
  - Solution: Accept BotApi via constructor parameter
  - Alternative: Manual testing with test Telegram bot

### Medium Priority
- ⏳ User leaves group mid-wager - No handling for this edge case yet
- ⏳ Network retry logic - Need retry mechanism for failed Telegram API calls
- ⏳ Frontend error boundaries - Better error handling in Vue components
- ⏳ LLM fallback testing - Ensure graceful degradation when LLM fails

### Low Priority
- ⏳ Optimize N+1 queries - Review all relationships for eager loading opportunities
- ⏳ Add database indexes - Performance optimization for frequent queries
- ⏳ LLM cache invalidation - Better cache key strategy for dynamic content

---

## Feature Backlog (Future Phases)

### Wager Features
- ⏳ Wager Templates - Pre-built templates for common wager types
- ⏳ Event-Specific Wagers - Meta-wagers about events

### Engagement Features
- ⏳ Wager Streaks - Consecutive participation rewards
- ⏳ Group Challenges - Monthly participation goals
- ⏳ Attendance Streaks - Consecutive event attendance bonuses

### Smart Notifications
- ⏳ Context-Aware Prompts (post-loss, pre-deadline, low balance, inactive friends)
- ⏳ Decay warnings (day 12 of inactivity)
- ⏳ All powered by LLM for personality

### Advanced Features (Phase 3)
- ⏳ Multi-Platform Support - Slack, Discord, WhatsApp integrations
- ⏳ Personal Analytics - Win rate, earnings over time, favorite wager types
- ⏳ Group Analytics - Most active members, trending topics, seasonal patterns
- ⏳ Cross-Group Seasons - Optional inter-group competition
- ⏳ User-Generated Templates - Share wager templates between groups
- ⏳ Mobile Native Apps - If web mobile UX proves insufficient
- ⏳ Automated Outcome Detection - Sports APIs for automatic settlement
- ⏳ Voice/Video Integration - LLM-powered voice messages for announcements

---

## Implementation Reference

For detailed implementation guidance, see:
- `./docs/BEATWAGER_IMPLEMENTATION_PLAN.md` - Architecture details, code examples
- `./app/Services/LLMService.php` - LLM service implementation
- `./app/Services/LogService.php` - Operational logging
- `./lang/en/messages.php` - Message metadata for LLM

---

## Notes

- Always run tests before committing: `make test`
- Document any new edge cases discovered in this file
- Update completion dates as tasks finish
- Keep TODO.md as single source of truth for current work
- Monitor LLM usage and costs in production
- See IMPLEMENTATION_PLAN.md for architecture patterns and code examples
