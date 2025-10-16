# BeatWager TODO

**Last Updated:** October 16, 2025
**Current Phase:** Phase 2 - Events & Engagement (Final Polish)
**Status:** Core features complete, Phase 2 backend + frontend implemented, testing & messaging enhancements remaining

---

## HIGH PRIORITY: Group Customization Features

### Custom Point Currency Names
- ⏳ Add `points_currency_name` field to groups table (default: "points")
- ⏳ Update all messaging to use group's custom currency name
  - "John won 100 kudos" instead of "John won 100 points"
  - "You earned 50 eth" instead of "You earned 50 points"
- ⏳ Update UI to display custom currency name in dashboards, wagers, transactions
- ⏳ Add group settings page for admins to customize currency name

### Bot Notification Preferences
- ⏳ Add `notification_preferences` JSON field to groups table
  - Birthday reminders (boolean, default: false)
  - Event reminders (boolean, default: true)
  - Wager reminders (boolean, default: true)
  - Weekly summaries (boolean, default: false)
- ⏳ Add group settings UI for notification toggles
- ⏳ Implement birthday reminder job (daily check, sends on birthdays)
- ⏳ Respect preferences in all notification jobs

### AI-Powered Bot Personality
- ⏳ Add `llm_api_key` field to groups table (encrypted, nullable)
  - Support for OpenAI-compatible APIs (OpenRouter, etc.)
- ⏳ Add `bot_tone` text field to groups table (nullable)
  - Stores personality/tone instructions for LLM
  - Examples: "sarcastic and witty", "professional and formal", "encouraging and supportive"
- ⏳ Add group settings UI for LLM configuration
  - API key input (encrypted storage)
  - Tone textarea with examples/suggestions
  - Test message button to preview tone
- ⏳ Create MessagePersonalizationService
  - Check if group has LLM configured
  - If yes: send message template + tone to LLM, use response
  - If no: use default message templates
  - Fallback gracefully if LLM fails
- ⏳ Apply to all bot messages (wagers, events, reminders, settlements)

### Group Settings Dashboard
- ⏳ Create `/group/{group}/settings` route (accessible to all group members)
- ⏳ GroupSettings.vue component with tabs:
  - General (currency name, description)
  - Notifications (birthday, event, wager toggles)
  - Bot Personality (LLM key, tone configuration)
  - Advanced (decay settings, penalty rules - future)
- ⏳ Add "Group Settings" link in navbar for group members
- ⏳ Note: Trust social dynamics, no role restrictions for now (can add admin roles later if needed)

---

## Current Work: Phase 2 Final Polish

### Testing - Events System
- ⏳ EventService unit tests (RSVP, attendance, bonus distribution)
- ⏳ Integration tests for RSVP flow
- ⏳ Integration tests for attendance recording flow
- ⏳ E2E tests for complete event lifecycle

### Telegram Messaging - Events
- ⏳ Event announcement messages to group (when created)
- ⏳ RSVP inline buttons in Telegram
- ⏳ Attendance prompt messages (auto-sent after event)
- ⏳ Attendance confirmation messages (when submitted)
- ⏳ Point bonus notification messages

### UI/UX Polish
- ⏳ Frontend error message improvements
- ⏳ Loading states on all async operations
- ⏳ Success/error toast messages
- ⏳ Confirmation dialogs for destructive actions
- ⏳ Skeleton loaders for data fetching

### Edge Cases
- ⏳ User leaves group mid-wager (need to handle)
- ⏳ Network errors during critical operations (retry logic needed)

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

### Performance Optimization
- ⏳ Database query optimization (add indexes where needed)
- ⏳ N+1 query prevention (eager loading review)
- ⏳ Redis caching for frequent reads
- ⏳ Queue all non-critical notifications
- ⏳ Asset optimization (minification, compression)
- ⏳ CDN setup for static assets

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

### Soft Launch
- ⏳ Onboard 1-2 test friend groups
- ⏳ Monitor usage for first 2 weeks
- ⏳ Gather feedback via surveys/interviews
- ⏳ Fix critical bugs from feedback
- ⏳ Iterate on UX based on user behavior

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

### Low Priority
- ⏳ Optimize N+1 queries - Review all relationships for eager loading opportunities
- ⏳ Add database indexes - Performance optimization for frequent queries

---

## Feature Backlog (Future Phases)

### Wager Features
- ⏳ Wager Templates - Pre-built templates for common wager types
- ⏳ Revenge Wagers - Quick rematch after losing
- ⏳ Event-Specific Wagers - Meta-wagers about events

### Engagement Features
- ⏳ Wager Streaks - Consecutive participation rewards
- ⏳ Group Challenges - Monthly participation goals
- ⏳ Attendance Streaks - Consecutive event attendance bonuses

### Smart Notifications
- ⏳ Context-Aware Prompts (post-loss, pre-deadline, low balance, inactive friends)
- ⏳ Decay warnings (day 12 of inactivity)

### Advanced Features (Phase 3)
- ⏳ Multi-Platform Support - Slack, Discord, WhatsApp integrations
- ⏳ Personal Analytics - Win rate, earnings over time, favorite wager types
- ⏳ Group Analytics - Most active members, trending topics, seasonal patterns
- ⏳ Cross-Group Seasons - Optional inter-group competition
- ⏳ User-Generated Templates - Share wager templates between groups
- ⏳ Achievement Badges - Milestones, special accomplishments
- ⏳ Mobile Native Apps - If web mobile UX proves insufficient
- ⏳ Automated Outcome Detection - Sports APIs for automatic settlement

---

## Notes

- Always run tests before committing: `make test`
- Document any new edge cases discovered in this file
- Update completion dates as tasks finish
- Keep TODO.md as single source of truth for current work
