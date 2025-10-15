# BeatWager TODO

**Last Updated:** October 15, 2025 (Evening)
**Current Phase:** Phase 1 (Week 7-8: Polish, Testing & Edge Cases)
**Status:** Core features complete, authentication modernized, UI polished

---

## Progress Overview

- ✅ **Week 1-2:** Foundation & Setup - COMPLETE
- ✅ **Week 3-4:** Wager Creation & Joining - COMPLETE
- ✅ **Week 5-6:** Settlement & Points - COMPLETE
- 🔄 **Week 7-8:** Polish, Testing & Edge Cases - IN PROGRESS
- ⏳ **Week 9-10:** Security, Performance & Soft Launch - PENDING

---

## Week 7-8: Polish, Testing & Edge Cases (IN PROGRESS)

### Comprehensive Error Handling
- ✅ Validation on all inputs (wager creation, joining, settlement)
- ✅ Service layer error handling
- ⏳ Frontend error message improvements
- ⏳ User-friendly error messages for all edge cases

### Edge Cases
- ✅ Creator doesn't settle (settlement reminders working)
- ⏳ User leaves group mid-wager (need to handle)
- ✅ Insufficient points when joining (validation working)
- ✅ Deadline in past (validation working)
- ✅ Duplicate join attempts (validation working)
- ⏳ Network errors during critical operations (retry logic needed)

### Testing
- ✅ Unit tests for core services (41 tests passing)
- ✅ WagerCreationFlowTest - All 12 tests passing
- ✅ EdgeCasesTest - 6 tests passing
- ✅ MessagingSystemTest - 8 tests passing
- ✅ PointEconomyTest - 6 tests passing
- ✅ SettlementReminderTest - 5 tests passing
- ✅ WagerSettlementTest - 1 test passing
- ⏳ Additional integration tests for key user flows
- ⏳ Load testing (simulate multiple concurrent users)

### UI Polish (Recent Improvements - Oct 15)
- ✅ **Create Wager Page Improvements**
  - ✅ Balance feasibility warning (shows users with insufficient balance)
  - ✅ Combined "Creating as" and "Group" display for space efficiency
  - ✅ Stake & deadline on single row (desktop responsive layout)
- ✅ **Wager Overview Page Improvements**
  - ✅ Real-time countdown with seconds (engaging live updates)
  - ✅ Display wager creator name
  - ✅ Participant balance column (current points)
  - ✅ "New Balance" column for settled wagers (complete transaction funnel)
  - ✅ Relative deadline time ("5d 2h ago")
  - ✅ Settlement note display
  - ✅ Settler info with timestamp
  - ✅ Winner medals (🥇🥈🥉) for top 3
  - ✅ Improved outcome clarity ("Outcome: yes" instead of "Settled: yes")
- ✅ **Messaging Consistency**
  - ✅ Standardized DM links (plain text + short URLs for both creation & progress)
  - ✅ Fixed duplicate button bug in wager announcements
- ⏳ Loading states on all async operations
- ⏳ Success/error toast messages
- ⏳ Confirmation dialogs for destructive actions
- ⏳ Skeleton loaders for data fetching
- ⏳ Optimistic UI updates where appropriate

### Mobile Responsive Design
- ⏳ Test all pages on mobile devices
- ⏳ Touch-friendly button sizes
- ⏳ Mobile navigation menu
- ⏳ Responsive table layouts

### Telegram Bot Commands
- ✅ /newbet - Working (generates creation token)
- ✅ /mybets - List user's active wagers (sends DM with preview + link to dashboard)
- ✅ /balance - Show current point balance (context-aware: group or all groups)
- ✅ /help - Bot command documentation (sends DM with link to help page)
- ⏳ /status - Group statistics (skipped for now - purpose unclear)

### Documentation
- ✅ Technical architecture documented
- ✅ API documentation (controllers, services)
- ✅ User guide (comprehensive help page at /help)
- ✅ Bot command reference (documented in /help and BOT_COMMANDS_IMPLEMENTATION.md)
- ✅ Bot commands implementation guide (docs/BOT_COMMANDS_IMPLEMENTATION.md)
- ⏳ Admin guide (dispute resolution, group management)

---

## Recent Achievements (Oct 15, 2025)

### Session-Based Authentication & UI Modernization (Oct 15 - Evening)
- ✅ **Platform-Agnostic Authentication System**
  - ✅ Created AuthenticateFromSignedUrl middleware (replaces platform-specific logic)
  - ✅ Session-based authentication (Laravel sessions, 24-hour persistence)
  - ✅ OneTimeToken support for bot commands (/mybets, /mybalance)
  - ✅ Signed URL support for wager links (encrypted user IDs)
  - ✅ Clean URLs after first visit (no tokens in navigation)
  - ✅ Future-proof for Discord, Slack, etc.
  - 📝 Benefits: Best practice, clean URLs, fast navigation, scalable

- ✅ **Tailwind CSS v4 Upgrade**
  - ✅ Upgraded from Tailwind v3 to v4.1.14
  - ✅ Configured @tailwindcss/vite plugin for Vite 7
  - ✅ Class-based dark mode with @custom-variant
  - ✅ All styling working and optimized
  - 📝 Benefits: Latest features, better performance, future-proof

- ✅ **Unified Navigation & Dark Mode**
  - ✅ Enhanced AppLayout with consistent navbar
  - ✅ User display (name or @username fallback for Telegram users without names)
  - ✅ Working dark mode toggle (sun/moon icon)
  - ✅ LocalStorage persistence for theme preference
  - ✅ Same navbar on /me (Dashboard) and /wager pages
  - 📝 Benefits: Consistent UX, modern feel, accessibility

- ✅ **Dashboard & Wager Page Polish**
  - ✅ Consistent max-w-4xl width across /me and /wager
  - ✅ Removed duplicate username display from dashboard
  - ✅ Condensed settled wager metadata to single line with bullet separators
  - ✅ Fixed missing deadline display for "Awaiting Settlement" wagers
  - ✅ Clean page hierarchy and visual consistency
  - 📝 Benefits: Professional appearance, better readability

- ✅ **Technical Improvements**
  - ✅ Simplified controllers (Auth::user() instead of manual validation)
  - ✅ Removed complex token generation overhead
  - ✅ Fixed memory leak in Show.vue (setInterval cleanup with onUnmounted)
  - ✅ Comprehensive documentation in claudedocs/
  - 📝 Benefits: Maintainable code, better performance, fewer bugs

### Bot Commands & User Dashboard (Oct 15 - PM)
- ✅ **Unified User Dashboard** - `/me` route with token authentication
  - ✅ DashboardController with show() and updateProfile() methods
  - ✅ Me.vue dashboard page with 4 tabs (Overview, Wagers, Transactions, Profile)
  - ✅ Stats overview: Total Balance, Active Wagers, Win Rate, Groups
  - ✅ Profile settings: Taunt Line and Birthday for bot automation
  - ✅ Real-time data with active/settled wagers, recent transactions
  - ✅ Token-based authentication (24-hour expiry, reusable)
  - 📝 Benefits: Single unified view, better UX, extensible architecture

- ✅ **Bot Commands Implementation** - Complete DM-based command system
  - ✅ `/mybets` - Sends DM with top 5 active wagers + dashboard link
  - ✅ `/balance` - Context-aware balance display (group or all groups)
  - ✅ `/help` - Comprehensive help message with link to full docs
  - ✅ All commands generate OneTimeToken with appropriate context
  - ✅ All commands create ShortUrls for clean messaging
  - ✅ Error handling for users who haven't started bot DM
  - 📝 Benefits: Consistent pattern, clean UX, easy to extend

- ✅ **Help Documentation Page** - `/help` route
  - ✅ Help.vue comprehensive documentation page
  - ✅ Getting Started guide with quick start
  - ✅ Detailed bot command reference
  - ✅ Wager types explained (Binary, Multiple Choice, Numeric)
  - ✅ How points work (starting balance, wagering, payouts)
  - ✅ Profile settings documentation
  - ✅ Comprehensive FAQ section
  - 📝 Benefits: Self-service user support, reduced confusion

- ✅ **User Profile Fields** - Birthday and taunt line support
  - ✅ Migration: add_profile_fields_to_users_table
  - ✅ Added `taunt_line` (text, nullable) for victory messages
  - ✅ Added `birthday` (date, nullable) for birthday automation
  - ✅ Updated User model with fillable and casts
  - 📝 Benefits: Personalization, future automation features

## Recent Achievements (Oct 15, 2025 - AM)

### System Architecture Improvements
- ✅ **Audit Log System** - Complete tracking and accountability
  - ✅ Created `audit_logs` table with polymorphic relationships
  - ✅ AuditLog model with query scopes (byActor, action, recent)
  - ✅ AuditService helper for easy logging
  - ✅ Integrated into WagerService (wager.created, wager.joined, wager.settled)
  - ✅ Tracks actor, action, auditable entity, metadata, IP, user agent
  - 📝 Benefits: Debugging, accountability, analytics, compliance, dispute resolution

- ✅ **Token System Unification** - Eliminated duplication
  - ✅ Unified WagerCreationToken and OneTimeToken into single model
  - ✅ Platform-agnostic JSON context field for flexibility
  - ✅ Single pattern for all token types (creation, settlement, events)
  - ✅ Updated controllers, services, and tests
  - 📝 Benefits: Less code, single source of truth, extensible design

### Docker & Development Environment
- ✅ **Fixed Docker Permission Issues** - No more EACCES errors
  - ✅ Configured Docker to run as host user (UID/GID 1001)
  - ✅ Updated Dockerfile with USER_ID/GROUP_ID build args
  - ✅ Updated docker-compose.yml with user directives
  - ✅ Files now created with correct ownership automatically
  - 📝 Best practice implementation, permanent fix

---

## Refactoring & Architecture Improvements (HIGH PRIORITY)

### Platform-Agnostic Refactoring
- ⏳ **Move telegram columns from users table to messenger_services table**
  - Create `messenger_services` table (user_id, platform, platform_user_id, username, first_name, etc.)
  - Migrate existing telegram_* columns from users table
  - Update User model relationships
  - Update authentication flow to use messenger_services

- ⏳ **Make groups platform agnostic**
  - Replace telegram_* columns with generic platform columns
  - Add `platform` enum column (telegram, slack, discord)
  - Rename: `telegram_chat_id` → `platform_chat_id`
  - Rename: `telegram_chat_title` → `platform_chat_title`
  - Rename: `telegram_chat_type` → `platform_chat_type`
  - Note: Each group belongs to ONE platform, but groups table can contain groups from ANY platform
  - Update Group model and MessengerFactory to use platform field

### PHP 8.3 Enums
- ⏳ **Create Enums in ./app/Enums/**
  - `WagerType` enum (binary, multiple_choice, numeric, date)
  - `WagerStatus` enum (open, settled, disputed, cancelled)
  - `WagerEntryResult` enum (pending, won, lost, tied)
  - `Platform` enum (telegram, slack, discord)
  - `TransactionType` enum (wager_join, wager_win, wager_refund, decay, weekly_bonus, etc.)
  - `UserRole` enum (participant, creator, admin)

- ⏳ **Update models to use Enums**
  - Wager model: use WagerType, WagerStatus
  - WagerEntry model: use WagerEntryResult
  - Transaction model: use TransactionType
  - User-Group pivot: use UserRole
  - Update database migrations to use enum columns

### Database Migration Cleanup
- ⏳ **Consolidate Schema::table() migrations**
  - Review all migrations in database/migrations/
  - Identify Schema::table() that modify tables
  - Merge changes into original Schema::create() migrations
  - Test with `php artisan migrate:fresh`
  - Delete redundant Schema::table() migration files
  - Document migration sequence for production (if needed)

### Makefile Extensions
- ✅ **Add useful commands to Makefile** - COMPLETE

---

## Week 9-10: Security, Performance & Soft Launch (PENDING)

### Security Audit
- ⏳ CSRF protection verification (already implemented, needs review)
- ⏳ SQL injection prevention check (parameterized queries review)
- ⏳ XSS protection verification (Vue handles most, review)
- ⏳ One-time token validation security review
- ⏳ Rate limiting on API endpoints
- ⏳ Telegram webhook validation (verify requests from Telegram)
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
- ⏳ Zero-downtime deployment strategy (blue-green)

### Production Deployment
- ⏳ Set up production server (VPS or cloud)
- ⏳ Configure PostgreSQL (managed service recommended)
- ⏳ Configure Redis
- ⏳ Set up queue workers with Supervisor
- ⏳ SSL certificate setup
- ⏳ Domain configuration
- ⏳ Load balancer configuration (for blue-green)

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
- ⏳ **TelegramWebhookController not unit tested** - Requires refactoring for dependency injection of BotApi
  - Current: `new BotApi()` in constructor makes it unmockable
  - Solution: Accept BotApi via constructor parameter
  - Alternative: Manual testing with test Telegram bot
  - Impact: Medium (webhook integration requires E2E testing)

### Medium Priority
- ⏳ **User leaves group mid-wager** - No handling for this edge case yet
- ⏳ **Network retry logic** - Need retry mechanism for failed Telegram API calls
- ⏳ **Frontend error boundaries** - Better error handling in Vue components

### Low Priority
- ⏳ **Optimize N+1 queries** - Review all relationships for eager loading opportunities
- ⏳ **Add database indexes** - Performance optimization for frequent queries

---

## Feature Backlog (Extracted from FIRST_CHAT.md)

### Additional Wager Features
- ⏳ **Wager Templates** - Pre-built templates for common wager types
  - Sports Match: "Will {team_a} beat {team_b}?"
  - Yes/No Question: "Will {event} happen by {date}?"
  - Achievement: "Who will {achieve X} first?"
  - Store templates in database, allow customization
  - Template selector in wager creation form

- ⏳ **Revenge Wagers** - Quick rematch after losing
  - Post-loss notification: "Lost to @Alice? Create revenge wager!"
  - Pre-filled quick-create flow targeting winner
  - Social pressure for winner to accept
  - Optional double-stakes mechanic

- ⏳ **Event-Specific Wagers** - Meta-wagers about events
  - "Will Dave actually show up this time?" (Yes/No)
  - "How many people will attend?" (Numeric)
  - "Who will arrive last?" (Multiple choice)
  - Auto-resolve based on event check-ins

### Engagement Features
- ⏳ **Wager Streaks** - Consecutive participation rewards
  - Track weeks with ≥1 wager joined
  - Bonus: +10pts per week (caps at 5 weeks = +50pts/week)
  - Display: "🔥 5 week streak!"
  - Breaks if week ends without participation

- ⏳ **Group Challenges** - Monthly participation goals
  - Admin sets challenges: "Everyone join 10 wagers → +200pts"
  - Group-wide progress tracking
  - Unlocks badges/achievements

- ⏳ **Attendance Streaks** - Consecutive event attendance
  - Track consecutive events attended
  - Bonus: +20pts per event in streak (e.g., 5 events = +100pts on 5th)
  - Display: "🔥 3 event streak! Don't break it!"

### Smart Notifications
- ⏳ **Context-Aware Prompts**
  - Post-loss: "Lost to @Alice? Create revenge wager!"
  - Pre-deadline: "3 of your wagers close tomorrow!"
  - Low balance: "You're down to 200pts. Join a smaller wager?"
  - Inactive friends: "Haven't seen @Bob in 2 weeks, challenge them!"
  - Decay warning: "Join a wager or lose points!" (day 12 of inactivity)

### Event System Enhancements
- ⏳ **No-Show Penalties** (Optional per group)
  - Soft: RSVP "Going" but don't check in → -50pts
  - Hard: Miss 2 consecutive events → point decay doubles for 2 weeks
  - Grace period: Can excuse before deadline

- ⏳ **Event Leaderboard**
  - Track "most social" member
  - Metrics: Points from events, attendance %, longest streak
  - Recognition: "👑 @Alice is Social Champion (8/8 events)"

### Advanced Features (Phase 3 Parking Lot)
- ⏳ **Multi-Platform Support** - Slack, Discord, WhatsApp integrations
- ⏳ **Personal Analytics** - Win rate, earnings over time, favorite wager types
- ⏳ **Group Analytics** - Most active members, trending topics, seasonal patterns
- ⏳ **Cross-Group Seasons** - Optional inter-group competition (if groups opt-in)
- ⏳ **User-Generated Templates** - Share wager templates between groups
- ⏳ **Achievement Badges** - Milestones, special accomplishments
- ⏳ **Mobile Native Apps** - If web mobile UX proves insufficient
- ⏳ **Automated Outcome Detection** - Sports APIs for automatic settlement

---

## Phase 2 Preparation (NOT STARTED)

**Do not begin until Phase 1 is complete and live for ≥2 weeks**

### Entry Criteria Checklist
- ⏳ Phase 1 deployed and stable for ≥2 weeks
- ⏳ At least 1 friend group using regularly (≥3 wagers/week)
- ⏳ <5% critical error rate
- ⏳ Settlement flow working smoothly (<10% dispute rate)
- ⏳ User feedback collected and analyzed

### Phase 2 Scope (From ROADMAP.md)
- ⏳ Point decay system (5% per week after 14 days inactivity)
- ⏳ Weekly participation bonus (50pts if ≥1 wager joined)
- ⏳ Seasons with leaderboards and prize descriptions
- ⏳ Events with attendance tracking (trust-based, challenge mechanism)
- ⏳ Dispute voting system (replaces manual admin resolution)
- ⏳ Wager templates (10-15 pre-built templates)

**DO NOT START PHASE 2 TASKS UNTIL PHASE 1 CRITERIA MET**

## Notes

- Always run tests before committing: `make test`
- Document any new edge cases discovered in this file
- Update completion dates as tasks finish
- Keep TODO.md as single source of truth for current work
