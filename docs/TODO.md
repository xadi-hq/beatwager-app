# BeatWager TODO

**Last Updated:** October 20, 2025
**Current Phase:** Phase 2 - Engagement & Advanced Features
**Status:** Core features complete, LLM integration complete, Seasons implemented, Scheduled messages complete

---

## HIGH PRIORITY: Core Commands & Message Infrastructure

### 0. Implement /leaderboard Command ✅ COMPLETE (Oct 20, 2025)
- ✅ **Current status**: Fully implemented
- ✅ Implement actual leaderboard display
  - Show top 10 players in group by points
  - Include win rate (W-L record with percentage)
  - Format with emojis (🥇 🥈 🥉 for top 3)
  - Link to full leaderboard on web with short URLs
  - Custom currency support
- ⏳ Weekly automated leaderboard posts (Monday mornings) (FUTURE)
  - Ties into `SendWeeklyRecap` job (Section 4)
  - Use same LLM personality for engagement
- ⏳ Generate personalized LLM commentary about standings (FUTURE - enhancement)

### 0b. Message Tracking & Anti-Spam System ✅ COMPLETE (Oct 20, 2025)
- ✅ Create `sent_messages` table migration
  - Fields: id, group_id, message_type, context_id (wager_id, event_id, etc.), sent_at, metadata
  - Message types: 'wager.announcement', 'engagement.prompt', 'birthday.reminder', 'weekly.recap', etc.
  - Purpose: Track what messages were sent to prevent spam/duplicates
- ✅ Create `MessageTrackingService`
  - `canSendMessage(Group $group, string $messageType, array $rules): bool`
  - Rules: max_per_day, cooldown_hours, per_context_limit
  - Example: Only 1 engagement prompt per wager per 24h
- ⏳ Integrate into all message-sending services (READY - Phase 2)
  - Check before sending engagement prompts, birthday reminders, etc.
  - Log successful sends to `sent_messages`
- ✅ **Bonus**: Use as LLM context for "last week we..."
  - Query recent messages for context injection
  - "Remember the Marathon bet from last Tuesday?"
  - `getRecentHistory()` method implemented

### 0c. Message Chunking & Delays (Joke Delivery)
- ⏳ Add delay capability to MessengerAdapter
  - `sendMessageWithDelay(OutgoingMessage $message, int $delaySeconds): void`
  - Use queued jobs for delayed delivery
- ⏳ Add support for message sequences
  - `sendMessageSequence(array $messages, int $delayBetween): void`
  - Example: "Know what's green and doesn't fly?" ...10sec... "A grass!"
- ⏳ LLM can request delays via special syntax
  - Parse `[DELAY:10]` markers in LLM responses
  - Split into multiple messages with delays

### 0d. External API Integration (GIFs & Jokes)
- ⏳ Add Giphy API integration for GIF search
  - Create `GiphyService` with `search(string $query): string` (returns GIF URL)
  - LLM can request GIFs via tool/function calling
  - Example: LLM generates "celebration.gif" → Giphy finds relevant GIF
- ⏳ Add JokeAPI integration
  - Create `JokeService` with `getRandom(string $category): string`
  - LLM can fetch jokes for engagement messages
  - Categories: programming, dad jokes, puns, etc.
- ⏳ Make APIs available to LLM via tool/function calling
  - Add to LLM context as available tools
  - "You can search for GIFs using giphy_search(query) and get jokes using get_joke(category)"

---

## HIGH PRIORITY: LLM Integration & Engagement

### 1. Badge System (US-009)
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

### 2. Grudge Memory System (US-006) ✅ COMPLETE (Oct 20, 2025)
- ✅ Create `GrudgeService` for recent history
  - `getRecentHistory(User $user1, User $user2, Group $group): array`
  - Query last 5 wagers between two users
  - Format context for LLM ("Sarah has beaten John 3 times in a row")
- ✅ Integrate grudge context into settlement messages
  - Pass grudge history to LLM via `MessageContext`
  - LLM generates dramatic commentary based on history
  - Automatically injected for 1v1 wagers in `MessageService::settlementResult()`
- ✅ Store wager outcomes in `audit_events` for faster queries
  - Already implemented in `WagerService::createAuditEvents()`
  - Uses `AuditEventService::wagerWon()` for 1v1 matches

### 3. Notification Jobs (Respect Preferences)
- ⏳ Create `SendBirthdayReminders` job (daily check)
  - Check `notification_preferences.birthday_reminders`
  - Send only to groups with preference enabled
  - **-7 days reminder**: "When are WE celebrating your upcoming 40th birthday John?" (opt-in per group)
  - Day-of message at 8am: Current implementation complete
- ⏳ Update decay warning job to respect preferences
  - Check `notification_preferences.wager_reminders`
- ⏳ Create `SendWeeklyRecap` job (weekly summary - Monday mornings)
  - Check `notification_preferences.weekly_summaries`
  - Generate engaging summary with LLM if configured
  - Stats: most wins, biggest pot, most active member, current point totals
  - **Personalized engagement**: Mention lowest ranking ("Come on John, this is your week!")
  - **Competitive motivation**: Mention leader ("Who's going to prevent Jane from becoming untouchable?")
  - Show planned season end date (if any season is active)

### 4. Code Quality & Organization
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

### 5. Custom Prize System (Seasons Focus)
- ⏳ Add `prize_structure` JSON column to `group_seasons` table
  - **Note**: Prizes primarily for seasons (winner/runner-up/loser of season)
  - Can optionally extend to individual high-stakes wagers
  - Examples: "Season loser buys drinks", "Winner gets trophy/embarrassing photo privileges"
- ⏳ Create Prize Configuration UI in season settings
  - Allow admins to set custom prizes for season placements
  - Examples: 1st place: bragging rights, 2nd: honorable mention, Last: buys round
- ⏳ Display prizes in season start/recap announcements
  - Include prize context in LLM-generated season messages
  - "Remember: Last place buys drinks at next meetup!"
- ⏳ Prize reminders in season end messages
  - Mention earned prizes when season concludes
  - Optional: Track prize fulfillment status

### 6. Engagement Follow-up Prompts (Context-Aware) ✅ COMPLETE (Oct 20, 2025)
- ✅ Create `SendEngagementPrompts` job (hourly check for stale wagers)
  - Detect open wagers with 0-1 participants after 24 hours
  - Generate LLM-powered encouragement: "Come on guys, no one's betting yet?"
  - Respect group `notification_preferences.engagement_prompts`
  - Integrated with `MessageTrackingService` for anti-spam (24h cooldown)
  - Scheduled hourly in `routes/console.php`
- ⏳ Target specific users based on activity patterns (FUTURE)
  - "John, you usually join these kinds of wagers!"
  - "Sarah hasn't placed a wager in 5 days - what's up?"
- ⏳ Vary messaging based on wager characteristics (FUTURE - LLM can already do this)
  - Small stakes: "Low risk, high fun - who's in?"
  - High stakes: "This is a big one, folks!"
  - Deadline approaching: "Only 2 hours left to join!"

### 7. Revenge Bet System (US-012)
- ⏳ Create `OfferRevengeBet` job (triggered after big losses)
  - Detect losses >100 points
  - Create auto-generated revenge bet opportunity
  - Send targeted message to loser with LLM personality
- ⏳ Add "Rematch" quick action to settled wager page
  - One-click to create reverse wager
  - Same stakes, opposite outcome

### 8. Long-tail Bet Reminders (US-014)
- ⏳ Create `RemindLongWagers` job (weekly check for wagers >30 days out)
  - Send reminder about upcoming long-dated wagers
  - "Don't forget: Marathon bet settles in 45 days!"
- ⏳ Special UI treatment for long wagers (badge/indicator)

---

## ✅ RECENTLY COMPLETED

### Display Commands (Oct 20, 2025)
- ✅ `/wagers` command - Show top 3 open wagers in group
- ✅ `/challenges` command - Show top 3 open challenges in group
- ✅ `/events` command - Show top 3 upcoming events in group
- All three commands include participant counts, deadlines, and link to view all

---

## Current Work: Phase 2 Final Polish
### Testing - Events System
- ⏳ EventService unit tests (RSVP, attendance, bonus distribution)
- ⏳ Integration tests for RSVP flow
- ⏳ Integration tests for attendance recording flow
- ⏳ E2E tests for complete event lifecycle

### Testing - General
- ⏳ Increase test coverage from 38% to 60%
- ⏳ Unit tests for all command/callback handlers
- ⏳ Authentication middleware edge case tests
- ⏳ Integration tests for critical flows

### Telegram Messaging - Events
- ⏳ Attendance prompt messages (auto-sent after event via scheduled job)

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
- ⏳ Secure environment variable handling
- ⏳ LLM API key encryption validation

### Performance Optimization
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

### Medium Priority
- ⏳ User leaves group mid-wager - No handling for this edge case yet
- ⏳ Network retry logic - Need retry mechanism for failed Telegram API calls
- ⏳ Frontend error boundaries - Better error handling in Vue components
- ⏳ LLM fallback testing - Ensure graceful degradation when LLM fails

### Low Priority
- ⏳ LLM cache invalidation - Better cache key strategy for dynamic content

---

## Feature Backlog (Future Phases)

### Open Answer Wagers (Deferred - Documented for Roadmap)
- ⏳ **Numeric Wagers** - Guess a number (e.g., "How many points will the Lakers score?")
  - Closest guess wins
  - Tie-breaking rules needed
  - UI for numeric input validation

- ⏳ **String Wagers** - Open-ended text answers (e.g., "Who will win the election?")
  - Creator defines valid answers via web form
  - Multi-select settlement (multiple correct answers possible)
  - Case-insensitive matching
  - UI for answer management

### Wager Features
- ⏳ Wager Templates - Pre-built templates for common wager types
- ⏳ Event-Specific Wagers - Meta-wagers about events

### Engagement Features
- ⏳ Wager Streaks - Consecutive participation rewards
- ⏳ Group Challenges - Monthly participation goals
- ⏳ Attendance Streaks - Consecutive event attendance bonuses

### Smart Notifications
- ⏳ Context-Aware Prompts (post-loss, pre-deadline, low balance, inactive friends)
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
- `./claudedocs/improvement-implementation-plan.md` - Code quality improvement plan
- `./claudedocs/implementation-progress-summary.md` - Current refactoring progress

---

## Notes

- Always run tests before committing: `make test`
- Document any new edge cases discovered in this file
- Update completion dates as tasks finish
- Keep TODO.md as single source of truth for current work
- Monitor LLM usage and costs in production
- See IMPLEMENTATION_PLAN.md for architecture patterns and code examples
- Code quality improvements tracked in `./claudedocs/` - 4 of 5 phases complete (80%)
