# BeatWager TODO

**Last Updated:** October 29, 2025

---

## 🔥 HIGH PRIORITY

_No high priority items at the moment. Check "NEEDS DECISION" section below._

---

## 🔍 NEEDS DECISION

### No-Show Penalties
**Time**: 6-8 hours
**Question**: Priority for v1 launch? Can handle manually for now.

- RSVP "Going" but no check-in → configurable penalty
- Consecutive misses → streak reset or point penalty
- Grace period/excuse mechanism

### Challenge/Dispute System
**Time**: 8-10 hours
**Question**: Can disputes be handled manually for v1?

- Dispute attendance claims
- Voting system for challenges
- Auto-expiry for unrecorded events (48h)

### Personal Analytics Modal
**Time**: 4-5 hours
**Question**: Triggered from Win Rate card on Me.vue?

- Win rate over time chart
- Earnings history graph
- Favorite wager types
- Point balance history
- Streaks and achievements

---

## 🔵 BACKLOG (When Time Permits)

### Code Quality
- Centralize Telegram callbacks to `/lang/en/system.php` for i18n prep (2h)
- PHP 8.3 Enums (WagerType, WagerStatus, Platform, etc.) (4h)

### Event Streak Enhancements
- Dedicated streak badges on user profile pages
- Streak visualization (graphs, progress bars)
- Streak leaderboard on group dashboard
- Streak protection feature (one grace skip per season)
- Seasonal streak competitions with prizes

### LLM Service Improvements
- Prompt versioning system for tracking custom instruction changes
- Implement tiktoken library for accurate token counting
- Variable cache TTLs by message type (social vs functional)

### Advanced Wager Type Enhancements (Post v1)
- Partial scoring for rankings (Kendall Tau, Spearman correlation)
- Configurable scoring weights for ranking positions
- AI-assisted answer matching for short_answer types
- Wager templates for common question patterns

### New Features
- Badge System (oracle, degen, shark, loyalist, referee, ghost)
- Revenge Bet System ("Rematch" after losses >100pts)
- Long-tail Bet Reminders (wagers >30 days out)
- External APIs (Giphy, JokeAPI for LLM)

---

## 🏗️ REFACTORING (Future)

### Platform-Agnostic Architecture
- Move telegram columns to `messenger_services` table
- Make groups platform agnostic
- Implement `MessagingPlatform` interface

---

## 🔒 PRE-LAUNCH CHECKLIST

### Security Audit
- CSRF protection verification
- SQL injection prevention
- XSS protection
- One-time token security review
- Environment variable handling
- LLM API key encryption

### Performance Optimization
- N+1 query prevention
- Redis caching strategy
- Queue non-critical notifications
- Asset optimization
- CDN setup

### Monitoring & Logging
- Error tracking (Sentry)
- Performance monitoring
- Webhook failure alerts
- Database monitoring
- Queue job monitoring
- LLM usage/cost monitoring

---

## 🚀 DEPLOYMENT

### CI/CD Pipeline
- GitHub Actions (tests, linting, type checking)
- Automated deployment
- Database migrations
- Rollback mechanism
- Zero-downtime strategy

### Production Setup
- Server provisioning
- Managed PostgreSQL
- Redis configuration
- Queue workers (Supervisor)
- SSL certificate
- Domain + load balancer

### Soft Launch
- Onboard 1-2 test groups
- Monitor for 2 weeks
- Gather feedback
- Fix critical bugs
- Iterate on UX

---

## 📝 USEFUL COMMANDS

```bash
# Run Tests
docker compose exec app php artisan test

# Point Reconciliation
docker compose exec app php artisan points:reconcile --dry-run

# Build Assets
npm run build

# Test Birthday Reminders
docker compose exec app php artisan test:birthday-reminders
docker compose exec app php artisan test:birthday-reminders --user=123

# Test Revival Messages
docker compose exec app php artisan activity:check --dry-run

# Send Scheduled Messages (birthdays, holidays)
docker compose exec app php artisan messages:send-scheduled --dry-run
```

---

## 📚 DOCUMENTATION

- [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md) - Architecture patterns
- [point-reconciliation.md](./point-reconciliation.md) - Monitoring details
- [claudedocs/streak-llm-integration.md](../claudedocs/streak-llm-integration.md) - Streak system details
- [claudedocs/llm-data-flow-analysis.md](../claudedocs/llm-data-flow-analysis.md) - LLM architecture

---

## ✅ COMPLETED

<details>
<summary>Recent Completions (October 2025)</summary>

**October 29:**
- ✅ Advanced wager types: All 6 types fully functional (binary, multiple_choice, numeric, date, short_answer, top_n_ranking)
  - Backend service layer with settlement logic for all types
  - Web UI complete: Create, Join, Settle, and Success pages
  - Winner selection: exact match vs closest guess for numeric/date types
  - Tests passing for all wager types
  - Assets built and ready for deployment

**October 28:**
- Event Attendance Streak System MVP
- Birthday Reminder System

</details>
