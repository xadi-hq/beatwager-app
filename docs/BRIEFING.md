# Team Briefing: Social Wagering Platform

**Meeting Date:** October 13, 2025  
**Document Version:** 1.0  
**Attendees:** Development Team  
**Duration:** ~30 minutes read + discussion

---

## TL;DR

We're building a **social wagering platform** where friend groups can bet points (not money) on outcomes they care about. Think: "Will Ajax beat PSV?" or "When will Dave finally get married?"

**The Hook:** It bridges digital competition with real-world friendships through events and seasons.

**Tech Stack:** Laravel + Inertia + Vue 3 + PostgreSQL + Redis (all in Docker)

**Timeline:** 8-10 weeks to Phase 1 MVP, another 6-8 weeks for Phase 2 engagement features

**Your Mission:** Ship a working product that 1-2 friend groups actually use and love.

---

## Context: Why Are We Building This?

### The Problem

Friend groups want to add stakes to their predictions and debates, but existing solutions are:
- Too gambling-focused (real money, regulatory nightmares)
- Too generic (not built for close-knit groups)
- Too complex (require too much setup)

### Our Solution

A **trust-based, points-only wagering platform** that:
- Lives where friends already chat (Telegram to start)
- Uses a web interface for complex interactions
- Keeps everything within the friend group (no cross-contamination)
- Makes real-world meetups part of the game (events with attendance bonuses)

### The Vision

Make friendly competition more fun and structured, while strengthening real-world social bonds.

**Not a gambling platform.** Not a crypto scheme. Not a get-rich-quick app.

Just friends having fun with stakes that matter to them.

---

## Product Overview

### Core Loop

```
1. Friend creates wager via web (one-time URL from Telegram)
   ↓
2. Wager posted to Telegram group with join buttons
   ↓
3. Friends join by clicking buttons (or replying with guesses)
   ↓
4. Deadline passes → creator gets settlement URL
   ↓
5. Creator selects outcome → points distributed
   ↓
6. Everyone sees results, points update
   ↓
7. Repeat!
```

### Key Features (Phase 1)

**Wagering:**
- Binary wagers only (yes/no questions)
- Fixed stakes (everyone pays same amount to join)
- Creator settles outcome
- Winner-takes-all or ties split

**Points:**
- Start with 1,000 points per group
- Points reserved when joining, distributed when settled
- Full transaction audit trail

**Integration:**
- Telegram bot for announcements and joins
- Web interface for creation and settlement
- One-time token auth (no passwords)

**Optional in Phase 1:**
- Numeric wagers (e.g., "How many goals?")
- Multiple choice wagers
- Point decay / bonuses
- Seasons / leaderboards
- Events / attendance tracking
- Other messengers (Slack, Discord)

---

## Technical Architecture

### Stack Decision: Laravel + Inertia + Vue

**Why?**
1. **Speed to market** - Laravel gives us queues, scheduler, migrations for free
2. **Team knows it** - No learning curve
3. **Perfect fit** - Form-heavy app, not real-time heavy
4. **Scheduled tasks** - Critical for us (decay, bonuses, reminders)

**Alternatives considered:**
- Fastify + Better-Auth + Vue SPA → **Rejected** (more setup, slower development)

### Architecture Principle: Platform Agnostic

**Critical concept:**

```
┌─────────────────────────────────────┐
│   MESSENGER (Thin Interface)       │
│   - Just sends/receives messages   │
│   - NO business logic              │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│   WEB (Primary Interface)          │
│   - Wager creation                 │
│   - Settlement                     │
│   - All complex interactions      │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│   BACKEND (All Logic)              │
│   - Services: Wager, Point, Event  │
│   - Validation, calculations       │
│   - Database transactions          │
└─────────────────────────────────────┘
```

**Why this matters:**
- Adding Slack later = just implement `SlackMessenger` interface
- All logic tested without touching Telegram API
- Can add mobile app without rewriting backend

### Service Layer Pattern

**ALL business logic goes in services, NOT controllers.**

```php
// ❌ BAD - Logic in controller
class WagerController {
    public function settle(Request $request, Wager $wager) {
        // 100 lines of settlement logic here
        // Calculating winners
        // Distributing points
        // Sending notifications
        // etc.
    }
}

// ✅ GOOD - Thin controller, fat service
class WagerController {
    public function settle(
        Request $request, 
        Wager $wager,
        WagerService $wagerService
    ) {
        $outcome = $request->validated('outcome');
        $wagerService->settleWager($wager, auth()->user(), $outcome);
        
        return redirect()->route('wagers.show', $wager);
    }
}
```

**Services we'll build:**
- `WagerService` - Create, join, settle wagers
- `PointService` - Balance, transactions, distribution
- `TokenService` - One-time tokens for URLs
- `MessengerInterface` + implementations (Telegram, future Slack)
- `EventService` - Phase 2
- `SeasonService` - Phase 2

### Docker Setup

**5 containers:**

```yaml
app:        # Laravel + Inertia + Vue
postgres:   # Database
redis:      # Cache + queues
queue:      # Laravel queue worker
scheduler:  # Laravel cron jobs
```

**Why 5 separate containers?**
- `queue` needs to run `php artisan queue:work` continuously
- `scheduler` needs to run `php artisan schedule:work` continuously
- Both need same codebase but different entry points
- Easy to scale independently later (5 queue workers, 1 scheduler)

**Development workflow:**
```bash
# Start everything
docker-compose up -d

# Watch logs
docker-compose logs -f app

# Run migrations
docker-compose exec app php artisan migrate

# Run tests
docker-compose exec app php artisan test

# Access database
docker-compose exec postgres psql -U wager_user wager_platform
```

---

## Database Schema Highlights

### Key Design Decisions

**UUIDs for primary keys** (not auto-increment)
- Better for distributed systems
- No ID enumeration attacks
- Can generate client-side if needed

**Transaction audit trail** (critical!)
- Every point movement logged
- `balance_after` on every transaction (reconciliation)
- Never delete transactions (soft delete at most)

**JSONB for flexible data** (PostgreSQL strength)
- Wager options for multiple choice
- Custom fields per group
- Season metadata

### Core Tables (Phase 1)

```
users
├─ id (uuid)
├─ telegram_id (bigint, unique)
├─ username
└─ timestamps

groups
├─ id (uuid)
├─ platform (telegram, slack, etc.)
├─ platform_group_id
└─ name

user_group (pivot with data)
├─ id (uuid)
├─ user_id → users
├─ group_id → groups
├─ current_points (integer)
├─ role (participant, creator, admin)
└─ timestamps

wagers
├─ id (uuid)
├─ group_id → groups
├─ creator_id → users
├─ question (text)
├─ type (binary, numeric, multiple_choice)
├─ stake (integer)
├─ deadline (timestamp)
├─ status (open, closed, settled, disputed)
└─ timestamps

wager_entries
├─ id (uuid)
├─ wager_id → wagers
├─ user_id → users
├─ position (for binary: yes/no)
├─ points_wagered (integer)
├─ points_won (integer, nullable)
└─ timestamps

transactions (audit trail)
├─ id (uuid)
├─ user_id → users
├─ group_id → groups
├─ amount (integer, can be negative)
├─ type (wager_join, wager_win, wager_loss, etc.)
├─ reference_id (uuid, nullable)
├─ balance_after (integer)
└─ created_at
```

### Indexes (Performance Critical)

```sql
-- Hot paths
CREATE INDEX idx_wagers_group_status ON wagers(group_id, status);
CREATE INDEX idx_wagers_deadline ON wagers(deadline);
CREATE INDEX idx_entries_wager ON wager_entries(wager_id);
CREATE UNIQUE INDEX idx_entries_unique ON wager_entries(wager_id, user_id);
CREATE INDEX idx_transactions_user_group ON transactions(user_id, group_id, created_at DESC);
```

---

## Scheduled Jobs (Critical!)

We have **a lot** of background tasks. This is where Laravel shines.

### Daily Jobs

**1 AM: Apply Point Decay** (Phase 2)
```php
// Users who haven't joined a wager in 14+ days lose 5% (min 50, max 100)
$schedule->job(ApplyPointDecay::class)->dailyAt('01:00');
```

### Hourly Jobs
**Cleanup Expired Tokens**
```php
// Delete one-time tokens older than 1 hour
$schedule->job(CleanupExpiredTokens::class)->hourly();
```

**Send Deadline Reminders**
```php
// "Your wager closes in 1 hour!"
$schedule->job(SendDeadlineReminders::class)->hourly();
```

**Event Attendance Prompts** (Phase 2)
```php
// "Who attended today's dinner?" (2 hours after event)
$schedule->job(SendEventAttendancePrompts::class)->hourly();
```

**Lock Event Attendance** (Phase 2)
```php
// After 24hr challenge window, lock attendance as final
$schedule->job(LockEventAttendance::class)->hourly();
```

### Weekly Jobs

**Sunday 11:59 PM: Distribute Bonuses** (Phase 2)
```php
// Give 50 points to anyone who joined ≥1 wager this week
$schedule->job(DistributeWeeklyBonuses::class)
    ->weekly()
    ->sundays()
    ->at('23:59');
```

**Why so many scheduled tasks?**
Because we're gamifying time-based behaviors. This is the engagement engine.

---

## Queue System

### Why Queues Are Essential

**Problem:** Telegram rate limits (30 messages/second)

**Scenario:** 50-person group, wager gets settled
- Need to send 50 notifications (though more likely just 1 to the group)
- If synchronous: 50 API calls before HTTP response
- If Telegram slow: User waits 10+ seconds
- If Telegram down: Request fails entirely

**Solution:** Queue all notifications

```php
// In controller (fast)
$wagerService->settleWager($wager, $outcome);
return redirect()->back(); // Instant response

// In service (dispatches jobs)
event(new WagerSettled($wager));

// In listener (queued)
class NotifyParticipants implements ShouldQueue {
    public function handle(WagerSettled $event) {
        foreach ($event->wager->entries as $entry) {
            SendTelegramNotification::dispatch($entry->user, ...);
        }
    }
}
```

### Queue Priorities

```
high    → Settlement notifications, critical actions
default → General notifications, wager announcements
low     → Weekly bonuses, non-urgent tasks
```

**Production setup:** Multiple workers
```bash
# 2 workers for high priority
php artisan queue:work redis --queue=high --tries=3

# 3 workers for default
php artisan queue:work redis --queue=default --tries=3

# 1 worker for low priority
php artisan queue:work redis --queue=low --tries=3
```

---

## Testing Strategy

### Test Pyramid

```
     ┌─────────┐
     │   E2E   │  10% - Full browser tests (Selenium)
     └─────────┘
   ┌─────────────┐
   │ Integration │  30% - Laravel feature tests
   └─────────────┘
 ┌─────────────────┐
 │  Unit Tests     │  60% - Service layer tests
 └─────────────────┘
```

### What to Test

**Unit Tests (Service Layer):**
```php
// Test business logic in isolation
test('user cannot join wager with insufficient points', function () {
    $user = User::factory()->create();
    $userGroup = UserGroup::factory()->create([
        'user_id' => $user->id,
        'current_points' => 50,
    ]);
    
    $wager = Wager::factory()->create(['stake' => 100]);
    
    expect(fn() => app(WagerService::class)->joinWager($wager, $user, ['position' => 'yes']))
        ->toThrow(InsufficientPointsException::class);
});
```

**Integration Tests (Full HTTP Flow):**
```php
test('complete wager lifecycle', function () {
    $creator = User::factory()->create();
    
    // Create wager
    $this->actingAs($creator)
        ->post('/wagers/create', [
            'question' => 'Will it rain?',
            'type' => 'binary',
            'stake' => 100,
            'deadline' => now()->addDay(),
        ])
        ->assertRedirect();
    
    $wager = Wager::first();
    
    // Another user joins
    $participant = User::factory()->create();
    $this->actingAs($participant)
        ->post("/wagers/{$wager->id}/join", ['position' => 'yes'])
        ->assertOk();
    
    // Creator settles
    $this->actingAs($creator)
        ->post("/wagers/{$wager->id}/settle", ['outcome' => 'yes'])
        ->assertRedirect();
    
    expect($wager->fresh()->status)->toBe('settled');
});
```

**What NOT to test:**
- Framework features (Laravel is already tested)
- Third-party packages
- Trivial getters/setters

### Coverage Goal

**Phase 1:** 70% coverage (focus on service layer)  
**Phase 2:** 80% coverage

Use Laravel's built-in parallel testing:
```bash
php artisan test --parallel
```

---

## Development Workflow

### Getting Started

```bash
# Clone repo
git clone [repo-url]
cd beatwager-app

# Copy environment file
cp .env.example .env

# Edit .env with your values
# DB_DATABASE=wager_platform
# DB_USERNAME=wager_user
# DB_PASSWORD=your_secure_password
# TELEGRAM_BOT_TOKEN=your_bot_token

# Start Docker containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Generate app key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Seed database (optional)
docker-compose exec app php artisan db:seed

# Build frontend assets
docker-compose exec app npm run dev

# Visit http://localhost:8000
```

### Daily Workflow

```bash
# Start containers
docker-compose up -d

# Watch frontend changes (hot reload)
docker-compose exec app npm run dev

# Watch queue jobs (for debugging)
docker-compose logs -f queue

# Run tests
docker-compose exec app php artisan test

# Stop containers
docker-compose down
```

### Branch Strategy

```
main        → Production (protected)
(staging    → Staging environment > later)
feature/*   → Feature branches (PR to staging)
bugfix/*    → Bug fixes (PR to staging or main)
```

**Workflow:**
1. Create feature branch from `staging`
2. Develop + test locally
3. PR to `staging`
4. Test on staging environment
5. PR from `staging` to `main`
6. Deploy to production

### Commit Messages

Use conventional commits:
```
feat: add numeric wager type
fix: prevent negative point balances
refactor: extract point calculation to service
test: add settlement edge case tests
docs: update API documentation
```

---

## Phase 1 Milestones

### Week 1-2: Foundation ✅
- [ ] Docker setup complete
- [ ] Database schema migrated
- [ ] Auth working (Telegram OAuth)
- [ ] Basic service layer structure
- [ ] Telegram webhook receiving updates

### Week 3-4: Core Wager Flow ✅
- [ ] Wager creation (web UI)
- [ ] Telegram announcement with buttons
- [ ] Join flow working (reserve points)
- [ ] Dashboard shows balance + active wagers

### Week 5-6: Settlement & Points ✅
- [ ] Settlement UI (web)
- [ ] Point distribution logic
- [ ] Transaction logging
- [ ] Notifications sent

### Week 7-8: Polish ✅
- [ ] Error handling comprehensive
- [ ] Edge cases covered
- [ ] Tests passing (70%+ coverage)
- [ ] UI responsive on mobile

### Week 9-10: Launch ✅
- [ ] Security audit complete
- [ ] Performance tested
- [ ] Documentation written
- [ ] 1-2 groups onboarded
- [ ] Monitoring in place

---

## Critical Success Factors

### What "Done" Looks Like (Phase 1)

**Technical:**
- ✅ 1-2 friend groups using it regularly (≥3 wagers/week)
- ✅ <5% error rate on critical flows
- ✅ Settlement within 48hrs of deadline (>80% of time)
- ✅ All scheduled jobs running successfully

**Product:**
- ✅ Users onboard without help
- ✅ Users understand how to create, join, settle wagers
- ✅ Disputes handled manually but tracked
- ✅ Positive feedback from test groups

**Team:**
- ✅ Confident in architecture decisions
- ✅ Codebase maintainable (new features easy to add)
- ✅ Test coverage sufficient (can refactor safely)

### What Failure Looks Like

- ❌ Test groups abandon after 1 week
- ❌ Too many settlement disputes (>20%)
- ❌ Users confused by flow (need hand-holding)
- ❌ Technical issues block usage (>10% error rate)
- ❌ Team wants to rewrite before Phase 2

### Key Risks

| Risk | Impact | Mitigation |
|------|--------|------------|
| **Telegram API breaks** | High | Abstract behind interface, monitor changelog |
| **Settlement disputes too frequent** | Medium | Clear rules, accept some manual resolution in Phase 1 |
| **Users don't understand flow** | High | Clear onboarding, help commands, docs |
| **Queue workers crash** | High | Supervisor process management, alerts |
| **Point calculation bugs** | Critical | Extensive tests, transaction audit trail |

---

## Team Responsibilities

### Backend Developer
- Service layer implementation
- Database migrations
- Queue job logic
- Telegram webhook handling
- API endpoints for Inertia
- Scheduled task implementation

### Frontend Developer
- Vue components (forms, dashboard)
- Inertia page components
- Tailwind styling
- Mobile responsiveness
- Loading states, error handling
- User flows

### Full-Stack (if applicable)
- Both of the above
- Integration between layers
- End-to-end feature implementation

### DevOps/Infrastructure
- Docker configuration
- Deployment pipeline
- Monitoring setup
- Database backups
- SSL certificates
- Server provisioning

---

## Communication & Cadences

### Daily Standup (15 min)
- What did you work on yesterday?
- What will you work on today?
- Any blockers?

### Weekly Planning (1 hour)
- Review last week's progress
- Plan next week's tasks
- Adjust timeline if needed
- Demo working features

### Bi-Weekly Retro (30 min)
- What went well?
- What could improve?
- Action items for next sprint

### Ad-hoc Discussions
- **Technical decisions:** Slack thread + document outcome
- **Blockers:** Immediately flag, don't wait for standup
- **Questions:** Ask early, don't spin wheels

---

## Documentation Expectations

### Code Documentation

**Services (must have):**
```php
/**
 * Settle a wager with the given outcome.
 * 
 * This method:
 * 1. Validates the settler is the creator
 * 2. Calculates winners based on outcome
 * 3. Distributes points to winners
 * 4. Creates transaction records
 * 5. Fires WagerSettled event (triggers notifications)
 * 
 * @param Wager $wager The wager to settle
 * @param User $settler The user settling (must be creator)
 * @param mixed $outcome The outcome (format depends on wager type)
 * 
 * @throws UnauthorizedException If settler is not creator
 * @throws WagerAlreadySettledException If already settled
 * @throws InvalidOutcomeException If outcome invalid for wager type
 * 
 * @return void
 */
public function settleWager(Wager $wager, User $settler, mixed $outcome): void
```

**Complex algorithms (must have):**
```php
// Calculate point distribution for numeric wagers
// 
// Algorithm:
// 1. Find entry with guess closest to actual value
// 2. If multiple tied for closest, split winnings equally
// 3. If no entries, refund all stakes
//
// Example:
//   Actual: 10
//   Guesses: 8, 10, 12, 10
//   Winners: [10, 10] (tied for exact)
//   Each wins: total_pool / 2
```

### Technical Decisions

**Use ADR (Architecture Decision Records):**
```markdown
# ADR-001: Use UUIDs for Primary Keys

## Status
Accepted

## Context
Need to decide between auto-increment integers vs UUIDs for primary keys.

## Decision
Use UUIDs (v4) for all primary keys.

## Consequences
**Positive:**
- Better for distributed systems
- No ID enumeration
- Can generate client-side

**Negative:**
- Slightly larger storage (16 bytes vs 8 bytes)
- Slightly slower joins (negligible at our scale)

## Alternatives Considered
- Auto-increment integers (rejected: ID enumeration, distributed issues)
- ULIDs (deferred: UUIDs sufficient for now)
```

---

## Resources & References

### Documentation

**Primary Docs:**
1. **PRD** (`PRD_START.md`) - Product vision, features, user stories
2. **Architecture** (`ARCHITECTURE.md`) - Tech stack, patterns, rationale
3. **Roadmap** (`ROADMAP.md`) - Phased plan, timelines, scope
4. **This Briefing** (`BRIEFING.md`) - Context, workflow, expectations

**External Docs:**
- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Telegram Bot API](https://core.telegram.org/bots/api)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)

### Tools & Access

**Required:**
- GitHub access (repo to be created)
- Docker Desktop or Docker Engine
- Telegram account (for testing)
- PostgreSQL client (TablePlus, pgAdmin, or CLI)

**Optional:**
- Laravel Telescope (debugging, local only)
- Redis Desktop Manager (view queue jobs)
- Postman/Insomnia (API testing)

---

## Questions & Answers

### Q: Can I use different tools than specified?

**A:** For IDE, editor, OS: absolutely. For tech stack (Laravel, Vue, PostgreSQL): no, we need consistency. If you think we should change something fundamental, propose it with rationale, but expect pushback unless compelling reason.

### Q: What if I get stuck on something for >2 hours?

**A:** Ask for help immediately. Don't be a hero. Post in Slack with:
- What you're trying to do
- What you've tried
- What error/issue you're seeing
- Any relevant code snippets

### Q: Can I refactor something that seems wrong?

**A:** Yes, but:
1. Make sure tests pass before refactoring
2. Keep refactor PRs separate from feature PRs
3. Explain the "why" in PR description
4. If it's a big refactor (>500 lines changed), discuss first

### Q: Do we really need all these scheduled jobs?

**A:** Yes. They're the engagement engine. Point decay, weekly bonuses, reminders - these create the behavioral loops that make the platform sticky. Without them, it's just a static wager tracker.

### Q: Why can't we add [feature X] to Phase 1?

**A:** Because we're disciplined. Phase 1 is about validating the core loop works. If users don't create, join, and settle binary wagers successfully, nothing else matters. We'll add [feature X] in Phase 2 if Phase 1 succeeds.

### Q: What if Telegram changes their API?

**A:** We're abstracted behind `MessengerInterface`. In worst case, we update `TelegramMessenger` implementation. The rest of the app doesn't change. This is why platform-agnostic architecture matters.

### Q: How do we handle database changes in production?

**A:** Migrations must be backwards compatible (Phase 1 especially). Never:
- Drop columns (add `nullable()` instead, clean up later)
- Rename columns (add new, migrate data, deprecate old)
- Change column types (risky)

Use two-phase migrations if needed.

---

## Expectations & Culture

### Coding Standards

**PHP:**
- PSR-12 style (enforced by `laravel/pint`)
- Run `./vendor/bin/pint` before committing
- Type hints on all method parameters and returns
- Strict types enabled (`declare(strict_types=1)`)

**JavaScript/Vue:**
- ESLint + Prettier (config provided)
- Composition API only (no Options API)
- `<script setup>` syntax preferred
- TypeScript for complex components (optional but encouraged)

**SQL:**
- Use migrations, never manual schema changes
- Always use parameter binding (Eloquent does this)
- Index anything used in WHERE/JOIN clauses

### Code Review Standards

**Every PR must:**
- [ ] Pass all tests
- [ ] Pass linter (Pint, ESLint)
- [ ] Have descriptive title and description
- [ ] Be <500 lines (split larger changes)
- [ ] Include tests for new logic
- [ ] Update docs if public API changes

**Reviewers must:**
- Review within 24 hours (or flag you're unavailable)
- Be constructive (suggest solutions, not just problems)
- Approve if "good enough" (don't bike-shed)
- Block if security/correctness issue

### When Things Go Wrong

**Production issues:**
1. Fix first, ask questions later (hotfix branch)
2. Document what happened (incident report)
3. Add test to prevent regression
4. Post-mortem if impactful (30 min meeting)

**Mistakes happen:**
- Broke staging? No big deal, just fix it.
- Broke production? It happens, fix it, learn from it.
- Deleted the database? We have backups (right?). Don't do it again.

**What we don't tolerate:**
- ❌ Hiding mistakes (always be transparent)
- ❌ Blaming others (we're a team)
- ❌ Cutting corners on security (no excuses)
- ❌ Pushing broken code knowingly (flaky tests count)

---

## Motivation & North Star

### Why This Matters

You're not building "yet another CRUD app." You're building something that:
- **Strengthens friendships** (real social bonds)
- **Encourages IRL meetups** (events with attendance bonuses)
- **Creates shared stories** ("Remember when Dave lost 500 points on that ridiculous bet?")

This is a **social product** disguised as a wagering platform.

### What Success Looks Like (Long-term)

**6 months from now:**
- 50+ friend groups using it regularly
- Users planning their year around seasons
- Groups creating events just to earn bonuses
- Users mentioning it to other friend groups ("You need to try this")

**Dream scenario:**
A friend group messages us: *"We've been using your platform for our monthly dinners for 6 months. It's completely changed how we engage. People who used to skip now show up because they want the points. Thanks for building this."*

That's the mission.

### Your Role

You're not just writing code. You're:
- Designing behavioral loops that encourage engagement
- Creating moments of delight (winning points, seeing leaderboard)
- Building trust systems (disputes, settlements)
- Enabling friendships to thrive

**Take pride in this.** Build it well. Ship it. Iterate based on real feedback.

---

## Next Steps

### Immediate (This Week)

1. **Read all three docs** (PRD, Architecture, Roadmap)
2. **Set up development environment** (Docker, dependencies)
3. **Get Telegram bot token** (talk to team lead)
4. **Create first migration** (users table)
5. **Deploy "Hello World"** (Telegram bot responds to `/start`)

### Week 1 Goals

- [ ] All developers have working local environment
- [ ] Database schema for core tables migrated
- [ ] Telegram webhook receiving and logging updates
- [ ] Basic auth flow (Telegram OAuth) working
- [ ] First Inertia page rendering

### Questions Before We Start?

**Ask now.** Don't wait until you're blocked.

Topics to clarify:
- Working hours / time zones?
- Communication channels (Slack, Discord, Email)?
- On-call expectations (if production breaks)?
- Access to staging/production servers?
- Budget for tools (if needed)?

---

## Let's Build This 🚀

Remember:
- **Ship fast** (Phase 1 in 8-10 weeks)
- **Stay focused** (no scope creep)
- **Test thoroughly** (don't ship broken code)
- **Communicate early** (blockers, questions, concerns)
- **Have fun** (this should be enjoyable)

We're building something genuinely useful and fun. Let's make it great.

---

**Questions? Concerns? Excited?**

Post in #wager-platform Slack channel or DM the team lead.

Welcome to the team! 🎉