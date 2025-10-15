# Implementation Roadmap: Social Wagering Platform

**Document Version:** 1.0  
**Date:** October 13, 2025  
**Status:** Active Planning

---

## Philosophy

This roadmap follows a disciplined, incremental approach:

1. **Phase 1:** ~70% of value with ~40% of features (core loop working)
2. **Phase 2:** +20% of value with +40% of features (engagement systems)
3. **Phase 3:** +10% of value with +20% of features (expansion & polish)

**Key Principles:**
- Ship working software fast
- Learn from real usage before building more
- Keep platform-agnostic architecture throughout
- No waterfall - adapt based on learnings

---

## Phase 1: Core MVP (8-10 weeks)

### Goal
Launch a functional wagering platform where a friend group can:
- Create binary wagers via web
- Join wagers via Telegram
- Settle wagers via web
- Track points and history

### Success Criteria
- 1-2 friend groups using it regularly for 2+ weeks
- ≥5 wagers created per group per week
- Zero critical bugs blocking core flow
- Settlement disputes handled manually but tracked

### Scope (Binary Wagers Only)

#### IN Scope ✅
**Core Wagering:**
- Create binary (yes/no) wagers via web UI
- Join wagers via Telegram inline buttons
- Settle wagers via web UI (one-time token)
- Point distribution (winner takes all, ties split)
- Basic dispute flagging (manual admin resolution)

**Point Economy:**
- Starting balance: 1,000 points per user per group
- Point reservation when joining
- Transaction audit trail
- Balance display

**User Management:**
- Telegram OAuth authentication
- User-group relationships
- Basic role system (participant, creator, admin)

**Messaging (Telegram Only):**
- Wager announcements with join buttons
- Settlement notifications
- Balance updates
- Bot commands: /newbet, /mybets, /balance, /help

**Web Interface:**
- Dashboard (balance, active wagers, history)
- Wager creation form (with template selector)
- Settlement interface
- Basic wager history view

**Infrastructure:**
- Laravel (v12) backend with service layer
- Vue 3 + Inertia frontend
- PostgreSQL (v17) database
- Redis for caching/queues
- Telegram Bot API webhook

#### OUT of Scope ❌
- Multiple wager types (numeric, multiple choice) → Phase 2
- Point decay → Phase 2
- Seasons → Phase 2
- Events → Phase 2
- Dispute voting → Phase 2
- Weekly bonuses → Phase 2
- Multiple messenger platforms → Phase 3
- Advanced analytics → Phase 3
- Mobile apps → Phase 3

### Technical Architecture Decisions

**Database:**
```
Core Tables (Phase 1):
- users
- groups
- user_group (with points)
- wagers (binary only)
- wager_entries
- transactions
- one_time_tokens
- wager_templates (binary only)

Deferred Tables (Phase 2+):
- disputes
- dispute_votes
- group_seasons
- group_events
- group_event_* tables
```

**Services Layer:**
```
Phase 1 Services:
- WagerService (create, join, settle - binary only)
- PointService (balance, reserve, distribute, transactions)
- TokenService (generate, validate, consume)
- MessagingService interface + TelegramMessenger

Phase 2+ Services:
- DisputeService
- SeasonService
- EventService
```

**Jobs & Scheduled Tasks:**
```
Phase 1:
- ProcessWagerSettlement (queued)
- CleanupExpiredTokens (daily)
- SendDeadlineReminders (hourly)

Phase 2:
- ApplyPointDecay (daily)
- DistributeWeeklyBonuses (weekly)
- SendEventAttendancePrompts (hourly)
- LockEventAttendance (hourly)
```

### Development Timeline

#### Week 1-2: Foundation & Setup
**Goals:** Project scaffolding, database, authentication, basic services

**Tasks:**
- [ ] Create Laravel project with Vue + Inertia + Tailwind
- [ ] Set up development environment (WSL path: `/home/xander/...` → `\\wsl.localhost\Ubuntu\home\xander\...`)
- [ ] Create Phase 1 database schema and migrations
- [ ] Implement Telegram OAuth flow
- [ ] Set up Redis and queue workers
- [ ] Create base service classes (Wager, Point, Token)
- [ ] Set up Telegram webhook endpoint
- [ ] Basic error logging and monitoring

**Deliverables:**
- Working local dev environment
- Database with core tables
- User can authenticate via Telegram
- Webhook receives Telegram updates

---

#### Week 3-4: Wager Creation & Joining
**Goals:** Users can create and join binary wagers

**Tasks:**
- [ ] Build WagerService (create, validate, join methods)
- [ ] Build PointService (balance, reserve, transaction logging)
- [ ] Build TokenService (generate creation tokens)
- [ ] Create wager creation Vue form component
- [ ] Implement template selector (3-5 binary templates)
- [ ] Build wager preview component
- [ ] Create Telegram announcement formatter
- [ ] Implement inline keyboard for joining
- [ ] Handle join button callbacks from Telegram
- [ ] Point validation on join (sufficient balance)
- [ ] Update wager announcement after joins
- [ ] Build web dashboard (list wagers, show balance)

**Deliverables:**
- User types /newbet in Telegram → receives web URL
- User fills form, creates wager → posted to Telegram group
- Users click join buttons → wager recorded, points reserved
- Dashboard shows active wagers and current balance

---

#### Week 5-6: Settlement & Points
**Goals:** Creators can settle wagers, points are distributed correctly

**Tasks:**
- [ ] Build settlement token generation (on deadline pass)
- [ ] Create binary settlement Vue form
- [ ] Implement settlement logic (calculate winners)
- [ ] Build point distribution algorithm (winner-takes-all, handle ties)
- [ ] Create transaction records for all point movements
- [ ] Send settlement notifications to all participants
- [ ] Update wager status to "settled"
- [ ] Build dispute flagging (simple button, creates admin task)
- [ ] Create transaction history view
- [ ] Add wager detail view (web)

**Deliverables:**
- Deadline passes → creator gets settlement URL
- Creator selects outcome → points distributed
- All participants notified
- Transaction history shows all movements
- Disputes can be flagged (manual resolution for now)

---

#### Week 7-8: Polish, Testing & Edge Cases
**Goals:** Handle all edge cases, comprehensive testing, UX polish

**Tasks:**
- [ ] Comprehensive error handling (all services)
- [ ] Validation on all inputs (wager creation, joining, settlement)
- [ ] Handle edge cases:
  - [ ] Creator doesn't settle (send reminders)
  - [ ] User leaves group mid-wager
  - [ ] Insufficient points when joining
  - [ ] Deadline in past
  - [ ] Duplicate join attempts
  - [ ] Network errors during critical operations
- [ ] Write unit tests (services layer)
- [ ] Write integration tests (key user flows)
- [ ] UI polish (loading states, success/error messages)
- [ ] Mobile responsive design (web interface)
- [ ] Telegram message formatting improvements
- [ ] Add bot commands: /mybets, /balance, /help, /status
- [ ] Documentation (user guide, bot commands)

**Deliverables:**
- All critical paths tested and working
- Error messages are clear and helpful
- UI works on mobile browsers
- Documentation for users

---

#### Week 9-10: Security, Performance & Soft Launch
**Goals:** Production-ready, secure, performant, launched with test group

**Tasks:**
- [ ] Security audit:
  - [ ] CSRF protection
  - [ ] SQL injection prevention (parameterized queries)
  - [ ] XSS protection (Vue handles this mostly)
  - [ ] One-time token validation
  - [ ] Rate limiting on API endpoints
  - [ ] Telegram webhook validation (verify requests from Telegram)
- [ ] Performance optimization:
  - [ ] Database query optimization (indexes)
  - [ ] N+1 query prevention (eager loading)
  - [ ] Redis caching for frequent reads
  - [ ] Queue all non-critical notifications
- [ ] CI/CD Pipeline:
  - [ ] GitHub Actions workflow for CI (tests, linting, type checking)
  - [ ] GitHub Actions workflow for blue-green deployment
  - [ ] Automated database migrations in deployment
  - [ ] Rollback mechanism for failed deployments
  - [ ] Zero-downtime deployment strategy
- [ ] Deploy to production:
  - [ ] Set up server (VPS or cloud)
  - [ ] Configure blue-green deployment infrastructure
  - [ ] Set up PostgreSQL (managed service recommended)
  - [ ] Configure Redis
  - [ ] Set up queue workers with Supervisor
  - [ ] SSL certificate
  - [ ] Domain configuration
  - [ ] Load balancer configuration (for blue-green)
- [ ] Monitoring & logging:
  - [ ] Error tracking (Sentry or similar)
  - [ ] Performance monitoring
  - [ ] Telegram webhook failure alerts
  - [ ] Deployment status notifications
- [ ] Onboard 1-2 test friend groups
- [ ] Monitor usage for first 2 weeks
- [ ] Gather feedback via surveys/interviews

**Deliverables:**
- Production deployment live and stable
- 1-2 friend groups actively using platform
- Monitoring and alerts working
- Feedback document with user insights

---

### Phase 1 Metrics to Track

**Usage:**
- Daily Active Users (DAU) per group
- Wagers created per week per group
- Average participants per wager
- Wagers settled within 48hrs of deadline

**Technical:**
- API response times (p50, p95, p99)
- Error rate
- Telegram webhook success rate
- Queue job processing time

**Business:**
- User retention (week 1 → week 2 → week 3)
- Time from wager creation to first join
- Settlement disputes (should be low)

---

## Phase 2: Engagement & Expansion (6-8 weeks)

### Goal
Add the features that drive long-term engagement and make the platform sticky.

### Phase 2 Entry Criteria
- ✅ Phase 1 deployed and stable for ≥2 weeks
- ✅ At least 1 friend group using regularly (≥3 wagers/week)
- ✅ <5% critical error rate
- ✅ Settlement flow working smoothly (<10% dispute rate)

### Scope

#### IN Scope ✅
**Expanded Wager Types:**
- Numeric wagers (closest guess wins)
- Multiple choice wagers (select from predefined options)
- Updated settlement logic for all types
- Expanded template library (10-15 templates)

**Engagement Systems:**
- Point decay (5% per week after 14 days inactivity)
- Weekly participation bonus (50pts for ≥1 wager joined)
- Decay warnings and notifications

**Seasons:**
- Season CRUD (web interface)
- Optional point resets at season start
- Live leaderboard during season
- Prize description (freeform, not enforced)
- Season-end final standings

**Events:**
- Event CRUD (web interface)
- Manual attendance recording (web form)
- Attendance challenge mechanism
- Challenge voting
- Event bonus distribution

**Disputes:**
- Dispute voting system (replaces manual admin resolution)
- Vote tallying and automatic resolution
- Penalties for incorrect reporting/false challenges

#### OUT of Scope ❌
- Multiple messenger platforms → Phase 3
- Event attendance streaks → Phase 3
- Event-specific wagers → Phase 3
- Revenge wagers → Phase 3
- Advanced analytics → Phase 3

### Development Timeline

#### Week 1-2: Expanded Wager Types
**Tasks:**
- [ ] Add numeric and multiple_choice to wager types enum
- [ ] Update wager creation form (conditional fields)
- [ ] Build numeric settlement component
- [ ] Build multiple choice settlement component
- [ ] Update point distribution logic (numeric: closest wins, ties split)
- [ ] Create new templates for numeric and multiple choice
- [ ] Test all wager type combinations
- [ ] Update Telegram announcement formatting for new types

**Deliverables:**
- Users can create numeric and multiple choice wagers
- Settlement works correctly for all three types
- 10-15 templates total

---

#### Week 3-4: Point Decay & Weekly Bonuses
**Tasks:**
- [ ] Add last_wager_joined_at to user_group table
- [ ] Build ApplyPointDecay scheduled job (daily, 1 AM)
- [ ] Implement decay logic (5%, min 50, max 100)
- [ ] Send decay warnings (day 12 of inactivity)
- [ ] Update last_wager_joined_at on every join
- [ ] Build DistributeWeeklyBonuses job (weekly, Sunday 11:59 PM)
- [ ] Track weekly participation per user
- [ ] Send weekly bonus notifications
- [ ] Add decay and weekly_bonus to transaction types
- [ ] Update dashboard to show activity status

**Deliverables:**
- Inactive users lose points after 2 weeks
- Active users receive 50pts weekly bonus
- Clear notifications about both systems

---

#### Week 5-6: Seasons & Events
**Tasks:**
- [ ] Create group_seasons table and model
- [ ] Build season CRUD (web interface)
- [ ] Implement leaderboard calculation
- [ ] Create season detail page with live leaderboard
- [ ] Send season start/end notifications
- [ ] Create group_events tables
- [ ] Build event CRUD (web interface)
- [ ] Create attendance recording form (web)
- [ ] Implement attendance bonus distribution
- [ ] Build challenge creation form
- [ ] Implement challenge voting interface
- [ ] Build vote tallying and resolution logic
- [ ] Send all event-related notifications
- [ ] Update dashboard to show seasons and events

**Deliverables:**
- Groups can create seasons with prize descriptions
- Live leaderboards show rankings
- Groups can create events and record attendance
- Attendance can be challenged and voted on

---

#### Week 7-8: Dispute Voting & Testing
**Tasks:**
- [ ] Create disputes and dispute_votes tables
- [ ] Update dispute flagging to trigger voting
- [ ] Build dispute voting interface (web)
- [ ] Implement vote counting (majority wins)
- [ ] Handle dispute resolution outcomes
- [ ] Apply penalties for false disputes
- [ ] Send dispute notifications to participants
- [ ] Comprehensive testing of all Phase 2 features
- [ ] Integration testing (combinations of features)
- [ ] Load testing (can it handle 10 active groups?)
- [ ] Bug fixes and polish
- [ ] Update documentation

**Deliverables:**
- Disputes resolved via community voting
- All Phase 2 features working together
- System tested and stable
- Documentation updated

---

### Phase 2 Success Criteria
- Decay system reduces inactive users or encourages participation
- Weekly bonuses given consistently without errors
- At least 1 group creates a season
- At least 1 group creates an event with attendance recorded
- Dispute voting resolves ≥80% of disputes without admin intervention
- <3% critical error rate
- All 3 wager types used regularly

---

## Phase 3: Future Exploration (Timeline TBD)

### Philosophy
**Do NOT plan Phase 3 in detail until Phase 2 is complete and live.**

After Phase 2, conduct a comprehensive review:

#### Review Questions
1. **What did we learn?**
   - Which features are most used?
   - Which features are ignored?
   - What problems did users complain about?
   - What features did users request that we didn't anticipate?

2. **What's working?**
   - Retention metrics
   - Engagement metrics
   - Technical stability
   - User satisfaction

3. **What should we build next?**
   - Double down on what's working?
   - Expand to new platforms (Slack, Discord)?
   - Build admin tools for managing multiple groups?
   - Add social features (wager feed, cross-group leaderboards)?
   - Something completely different based on learnings?

### Potential Phase 3 Ideas (Not Commitments)

**Multi-Platform Support:**
- Slack messenger implementation
- Discord messenger implementation
- WhatsApp integration (if technically feasible)
- Verify platform abstraction layer scales well

**Advanced Engagement:**
- Event attendance streaks
- Group challenges (monthly participation goals)
- Revenge wagers (quick rematch after losing)
- Event-specific wagers (meta-wagers about the event)

**Analytics & Insights:**
- Personal analytics (win rate, earnings over time, favorite wager types)
- Group analytics (most active members, trending topics)
- Seasonal trends and patterns

**Admin & Scaling:**
- Advanced admin panel (manage multiple groups)
- Dispute mediation tools
- User support ticketing
- Bulk operations

**Social Features:**
- Wager feed (see popular wagers from other groups, if opt-in)
- Cross-group seasons (if groups want to compete)
- Wager templates shared by users
- Achievement badges

**Technical Improvements:**
- Mobile native apps (if web mobile UX insufficient)
- Performance optimization for 100+ concurrent groups
- Advanced caching strategies
- Automated outcome detection (sports APIs for specific wagers)

**Business Model:**
- Freemium features?
- Group subscriptions?
- White-label for organizations?

### Phase 3 Decision Point

After Phase 2 is live and stable for 4+ weeks:
1. Analyze all usage data
2. Review all user feedback
3. Conduct user interviews
4. Write Phase 3 PRD based on learnings
5. Re-prioritize features based on real-world insights
6. Either continue building or pivot strategy

**Remember:** The features we think we need now may not be what users actually need. Stay flexible.

---

## Risk Management Throughout Phases

### Phase 1 Risks

**Technical:**
- Telegram API changes breaking integration
  - *Mitigation:* Abstract Telegram behind interface, monitor API changelog
- Database performance with many concurrent wagers
  - *Mitigation:* Proper indexing, query optimization, load testing

**Product:**
- Users don't understand how to use it
  - *Mitigation:* Clear onboarding, help commands, documentation
- Settlement disputes too frequent (manual admin overhead)
  - *Mitigation:* Clear settlement rules, accept some disputes in Phase 1, automate in Phase 2

**Operational:**
- Server costs higher than expected
  - *Mitigation:* Start with single VPS, scale only when needed
- Support requests overwhelming
  - *Mitigation:* Self-service help, clear error messages, FAQ

### Phase 2 Risks

**Technical:**
- Point decay calculations causing edge cases
  - *Mitigation:* Thorough testing, dry-run mode before live
- Season leaderboard performance with large groups
  - *Mitigation:* Cached calculations, async updates

**Product:**
- Decay feels punishing, users leave
  - *Mitigation:* Monitor churn, adjust parameters (grace period, rate)
- Seasons create toxic competition
  - *Mitigation:* Emphasize fun over competition, allow opt-out

**Legal:**
- Season prizes create gambling concerns
  - *Mitigation:* Strong disclaimers, platform never handles money, self-organized only

### Phase 3 Risks

**To be determined based on Phase 2 learnings**

---

## Platform-Agnostic Architecture Commitments

Throughout all phases, maintain strict separation:

### Messenger Layer (Thin Interface)
**Responsibilities:**
- Receive commands and callbacks
- Send formatted messages
- Parse user input

**NOT Responsible For:**
- Business logic
- Point calculations
- Wager validation
- Any decision-making

### Web Layer (Primary Interface)
**Responsibilities:**
- All complex interactions (creation, settlement, disputes)
- User authentication
- Data visualization
- Configuration

### Backend (Business Logic)
**Responsibilities:**
- All rules enforcement
- Point economy
- Wager lifecycle
- Transaction integrity
- Data persistence

This separation ensures:
- Adding new messengers is straightforward
- Core logic is testable without UI
- UI can be web, mobile app, or API in future

---

## Feature Freeze Commitment

**No new features will be added to this roadmap until:**
- Phase 1 is complete and live
- Phase 2 is complete and live (before planning Phase 3)

**Exceptions:**
- Critical bug fixes
- Security vulnerabilities
- Obvious must-have features discovered during development (requires team discussion)

**When users request features:**
- Document in "Future Exploration" parking lot
- Do NOT commit to building
- Evaluate after current phase completes

---

## Success Definition

### Phase 1 Success
✅ One friend group using it regularly for 1+ month  
✅ ≥10 wagers created and settled successfully  
✅ Users can onboard without help  
✅ <5% error rate on critical flows  
✅ Team confident in architecture decisions  

### Phase 2 Success  
✅ Multiple friend groups (3+) using regularly  
✅ Engagement systems (decay, bonuses) working as intended  
✅ At least 1 group completes a season  
✅ At least 1 group uses events feature  
✅ <3% error rate  
✅ Users report platform is "sticky"  

### Phase 3 Success
TBD based on Phase 3 goals (written after Phase 2)

---

## Next Steps

1. **Review this roadmap** - Ensure alignment on scope and priorities
2. **Validate Phase 1 scope** - Can we really ship this in 8-10 weeks?
3. **Set up development environment** - Get Laravel + Vue + Telegram working locally
4. **Create project repository** - Initialize git, set up CI/CD basics
5. **Start Week 1 tasks** - Begin foundation work

**Let's ship Phase 1!** 🚀