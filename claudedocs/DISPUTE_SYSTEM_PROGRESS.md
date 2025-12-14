# Dispute/Fraud Reporting System - Implementation Progress

## Status: Phase 13 of 13 - COMPLETE ✅

**Started**: 2025-12-13
**Last Updated**: 2025-12-14

---

## Confirmed Design Decisions

### Dispute Rules
| Aspect | Rule |
|--------|------|
| Who can dispute | Any group member |
| Vote threshold | Group ≤3: 1 vote, Group >3: 2 votes |
| Voting window | 48 hours |
| Dispute window | 72 hours after settlement |
| Fraud tracking | Global per `messenger_services.platform` + `platform_user_id` |

### Penalty Matrix
| Scenario | Who | Penalty |
|----------|-----|---------|
| False dispute | Reporter | -10% currency |
| Honest mistake (self-reported) | Settler | -5% currency |
| Confirmed fraud (1st offense) | Settler | -25% currency + outcome corrected |
| Confirmed fraud (repeat) | Settler | -50% currency + outcome corrected |
| Premature settlement (1st) | Settler | -25% currency + banned from item + outcome cleared |
| Premature settlement (repeat) | Settler | -50% currency + banned from item + outcome cleared |

### Key Behaviors
- "Honest mistake" option only available when reporter = settler (self-report)
- Fraud offense count tracked globally per platform identity
- Disputed items are locked during resolution
- Premature settlement clears outcome and allows re-settlement

### Disputable Types (Refined Session 2)
| Type | Disputable? | Reason |
|------|-------------|--------|
| Wagers | ✅ Yes | Settlement outcome can be wrong |
| 1:1 Challenges | ❌ No | Built-in 2-party verification (payer approves) |
| Super Challenges | ❌ No | Auto-approve mechanism handles verification |
| Elimination Challenges | ✅ Yes | Self-reporting fraud (didn't tap out when should have) |

### Elimination Dispute Flow
- **Reporter**: Any group member who witnessed the violation
- **Accused**: Survivor who allegedly should have tapped out
- **Original outcome**: "survivor" (currently active)
- **Vote options**: "should_be_eliminated" vs "currently_valid"
- **Resolution**: Force elimination via `EliminationChallengeService::forceElimination()`

---

## Phase Progress

### Phase 1: Database Schema
- [x] 1.1 Create `disputes` migration
- [x] 1.2 Create `dispute_votes` migration
- [x] 1.3 Add fraud tracking to `messenger_services` migration
- [x] 1.4 Add `dispute_id` to settleable models migration

### Phase 2: Enums
- [x] 2.1 Create `DisputeStatus` enum
- [x] 2.2 Create `DisputeResolution` enum
- [x] 2.3 Create `DisputeVoteOutcome` enum
- [x] 2.4 Extend `TransactionType` enum with dispute types

### Phase 3: Models
- [x] 3.1 Create `Dispute` model
- [x] 3.2 Create `DisputeVote` model
- [x] 3.3 Add fraud methods to `MessengerService` model
- [x] 3.4 Create `Disputable` trait for Wager, Challenge (GroupEvent deferred)

### Phase 4: Services
- [x] 4.1 Create `DisputeService` (core logic)
- [x] 4.2 Extend `PointService` with `deductPercentage()`
- [x] 4.3 Add reversal methods to `WagerService`
- [x] 4.4 Add reversal methods to `ChallengeService`

### Phase 5: Events & Listeners
- [x] 5.1 Create `DisputeCreated` event
- [x] 5.2 Create `DisputeResolved` event
- [x] 5.3 Create `DisputeVoteReceived` event
- [x] 5.4 Create `SendDisputeNotification` listener
- [x] 5.5 Create `SendDisputeResolutionNotification` listener
- [x] 5.6 Create `CheckDisputeThreshold` listener
- [x] 5.7 Add dispute message methods to `MessageService`
- [x] 5.8 Add dispute message templates to `lang/en/messages.php`

### Phase 6: API Routes & Controllers
- [x] 6.1 Add dispute routes
- [x] 6.2 Create `DisputeController`
- [x] 6.3 Inline validation (project pattern - no separate form requests)

### Phase 7: Frontend Components
- [x] 7.1 Create `Dispute/Show.vue` page (voting interface)
- [x] 7.2 Create `DisputeButton.vue` component
- [x] 7.3 Create `DisputeStatusBadge.vue` component

### Phase 8: Page Integration
- [x] 8.1 Integrate into `Wager/Show.vue`
- [x] 8.2 ~~Integrate into `Challenge/Show.vue`~~ N/A - 1:1/Super challenges not disputable
- [x] 8.3 Integrate into `Elimination/Show.vue`
- [x] 8.4 ~~Integrate into Event pages~~ N/A - Events not disputable

### Phase 9: Notifications
- [x] 9.1 Add dispute message templates to `MessageService` (completed in Session 2)
- [x] 9.2 Dispute created notification (SendDisputeNotification listener)
- [x] 9.3 Vote reminder notification (24h) - SendDisputeReminders command
- [x] 9.4 Resolution notification (SendDisputeResolutionNotification listener)

### Phase 10: Scheduled Tasks
- [x] 10.1 Create `ExpireDisputes` command
- [x] 10.2 Register in scheduler (routes/console.php)
- [x] 10.3 Create `SendDisputeReminders` command

### Phase 11: Settlement Reversal Logic
- [x] 11.1 Implement wager settlement reversal (completed in Session 2 - Phase 4.3)
- [x] 11.2 Implement challenge verification reversal (completed in Session 2 - Phase 4.4)
- [x] 11.3 Implement user ban from item (WagerService::clearSettlementAndBanUser, ChallengeService::clearSettlementAndBanUser)
- [x] 11.4 Implement outcome correction flow (DisputeService::correctOutcome, clearSettlement)

### Phase 12: Edge Cases & Guards
- [x] 12.1 Prevent multiple active disputes on same item (Disputable::canBeDisputed checks isDisputed)
- [x] 12.2 Handle user leaving group during dispute (HandleDisputeParticipantLeft listener + canUserVote checks)
- [x] 12.3 Handle dispute expiration with no/insufficient votes (handleExpiredDispute in DisputeService)
- [x] 12.4 Validate self-report flow restrictions (is_self_report flag + applyHonestMistakePenalty)

### Phase 13: Testing ✅
- [x] 13.1 Unit tests for `DisputeService` (16 tests)
- [x] 13.2 Unit tests for penalty calculations (17 tests)
- [x] 13.3 Feature tests for dispute creation (10 tests)
- [x] 13.4 Feature tests for voting flow (16 tests)
- [x] 13.5 Feature tests for resolution paths (12 tests)

---

## Session Log

### Session 1 (2025-12-13)
- Initial planning and design discussion
- Confirmed all rules and penalty matrix
- Created implementation plan
- Completed Phase 1: Database Schema (4 migrations)
- Completed Phase 2: Enums (3 new enums + TransactionType extension)
- Completed Phase 3: Models (Dispute, DisputeVote, Disputable trait)
- Completed Phase 4: Services (DisputeService, PointService extension, reversal methods)

### Session 2 (2025-12-13)
- Completed Phase 5: Events & Listeners
  - Created 3 events (DisputeCreated, DisputeResolved, DisputeVoteReceived)
  - Created 3 listeners (SendDisputeNotification, SendDisputeResolutionNotification, CheckDisputeThreshold)
  - Registered events in EventServiceProvider
  - Added dispute message methods to MessageService (disputeCreated, disputeResolved, disputeVoteReminder)
  - Added dispute message templates to lang/en/messages.php
- Design refinement: Scoped disputes to Wagers + Elimination Challenges only
  - Removed 1:1 challenges (built-in verification)
  - Removed Super Challenges (auto-approve mechanism)
  - Added elimination-specific dispute flow
- Updated Challenge model with elimination-aware `canBeDisputed()` logic
- Added `forceElimination()` method to EliminationChallengeService
- Added `createEliminationDispute()` method to DisputeService
- Updated DisputeService `correctOutcome()` to handle elimination disputes

### Session 3 (2025-12-13)
- Completed Phase 6: API Routes & Controllers
  - Added dispute routes to routes/web.php
    - GET /disputes/{dispute} - Show dispute details
    - POST /wager/{wager}/dispute - Create wager dispute
    - POST /elimination/{challenge}/dispute - Create elimination dispute
    - POST /disputes/{dispute}/vote - Cast vote
  - Created DisputeController with show(), createWagerDispute(), createEliminationDispute(), vote() methods
  - Used inline validation (project pattern - no separate Form Request classes)
- Completed Phase 7: Frontend Components
  - Created `Dispute/Show.vue` page with full voting interface
    - Vote progress tracking with countdown timer
    - Three vote options: Original Correct, Different Outcome, Too Early
    - Dynamic outcome selection for "Different Outcome" votes
    - Vote history display
    - Resolution descriptions
  - Created `DisputeButton.vue` component for initiating disputes
    - Drawer-based confirmation UI
    - Survivor selection for elimination disputes
    - Links to existing disputes
  - Created `DisputeStatusBadge.vue` component for status display
- Completed Phase 8: Page Integration
  - Updated `WagerController::show()` to pass `canDispute` and `dispute` data
  - Updated `EliminationChallengeController::show()` to pass `canDispute`, `dispute`, and `survivors` data
  - Integrated DisputeButton and DisputeStatusBadge into `Wager/Show.vue`
    - Status badge shown next to outcome for settled wagers with disputes
    - DisputeButton shown for settled wagers (with appropriate state)
    - Responsive design for both mobile and desktop layouts
  - Integrated DisputeButton and DisputeStatusBadge into `Elimination/Show.vue`
    - Status badge shown in header for challenges with disputes
    - DisputeButton shown for completed challenges (with survivor selection)
  - Marked 1:1/Super Challenge and Event integration as N/A (not disputable per design)

### Session 4 (2025-12-14)
- Completed Phase 9: Notifications
  - Verified existing implementation from Session 2:
    - `MessageService::disputeCreated()` - generates dispute creation message with voting buttons
    - `MessageService::disputeResolved()` - generates resolution notification
    - `MessageService::disputeVoteReminder()` - generates 24h reminder message
    - `SendDisputeNotification` listener - sends to group on DisputeCreated event
    - `SendDisputeResolutionNotification` listener - sends to group on DisputeResolved event
  - Created `SendDisputeReminders` command for 24h vote reminders
  - Added `reminder_sent_at` column to disputes table for tracking
- Completed Phase 10: Scheduled Tasks
  - Created `ExpireDisputes` command to handle 48h voting window expiration
  - Created `SendDisputeReminders` command for vote reminder notifications
  - Registered both commands in scheduler (routes/console.php):
    - `disputes:expire` - runs hourly
    - `disputes:send-reminders` - runs every 4 hours
- **Gap Fix: Callback Handlers**
  - Created `DisputeVoteCallbackHandler` for Telegram button voting
    - Handles `dispute_vote:{id}:{vote_type}` callbacks
    - Validates voter eligibility (not reporter/accused, not already voted)
    - Redirects to web UI for "different_outcome" votes requiring outcome selection
  - Created `DisputeViewCallbackHandler` for viewing dispute details
    - Handles `dispute_view:{id}` callbacks
    - Sends signed URL via DM
  - Registered both handlers in `AppServiceProvider`
- **Verified Phase 11: Settlement Reversal Logic** (implemented in Session 2)
  - `WagerService::reverseAndResettleWager()` + `reverseSettlementTransactions()`
  - `ChallengeService::reverseAndResettleChallenge()` + `reverseCompletionTransaction()`
  - `WagerService::clearSettlementAndBanUser()` (removes entry, refunds)
  - `ChallengeService::clearSettlementAndBanUser()` (resets to accepted state)
  - `DisputeService::correctOutcome()` + `clearSettlement()` (routing logic)
- **Verified Phase 12: Edge Cases & Guards** (implemented in earlier sessions)
  - 12.1: `Disputable::canBeDisputed()` checks `isDisputed()` → prevents duplicate disputes
  - 12.2: `Dispute::canUserVote()` checks group membership → handles user leaving
  - 12.3: `DisputeService::handleExpiredDispute()` → resolves with votes or defaults to OriginalCorrect
  - 12.4: `is_self_report` flag + `applyHonestMistakePenalty()` → 5% penalty for self-reports
- **Enhanced 12.2: Explicit User Leave Handling**
  - Created `HandleDisputeParticipantLeft` listener
    - Auto-dismisses disputes when accused leaves group (resolves as OriginalCorrect)
    - Reporter leaving: dispute continues (vote can still proceed)
    - Voter leaving: their vote still counts
  - Hooked into `TelegramWebhookController::removeUserFromGroup()` before detachment
  - Clears `dispute_id` from disputable item and restores status if needed

### Session 5 (2025-12-14)
- Completed Phase 13: Testing (67 tests total)
  - Created `DisputeFactory` and `DisputeVoteFactory` for test data
  - Created `tests/Unit/Services/DisputeServiceTest.php` (16 tests)
    - Dispute creation, voting, resolution, expiration handling
  - Created `tests/Unit/Services/DisputePenaltyCalculationTest.php` (17 tests)
    - Penalty percentages, fraud tracking, MessengerService methods
  - Created `tests/Feature/DisputeCreationTest.php` (10 tests)
    - Wager disputes, elimination disputes, validation rules
  - Created `tests/Feature/DisputeVotingFlowTest.php` (16 tests)
    - Voter eligibility, vote outcomes, threshold resolution
  - Created `tests/Feature/DisputeResolutionPathsTest.php` (12 tests)
    - All resolution paths, expired handling, user leaving group
- All tests passing: 67 dispute-related tests with 194 assertions

---

## Notes & Blockers

### Resolved Issues
- **Bug Fix (Session 2)**: Fixed undefined method `transferToPayee()` in `ChallengeService::reverseAndResettleChallenge()` - should be `settleHoldToAcceptor()`
- **Design Note**: Challenge "banning" differs from Wager "banning" - challenges reset state for re-verification instead of removing participants (1:1 constraint)
- **Design Refinement (Session 2)**: Scoped disputes to only Wagers and Elimination Challenges. 1:1 and Super Challenges have built-in protections that make disputes unnecessary.

---

## File References

### Created Files
- `database/migrations/2025_12_13_000001_create_disputes_table.php`
- `database/migrations/2025_12_13_000002_create_dispute_votes_table.php`
- `database/migrations/2025_12_13_000003_add_fraud_tracking_to_messenger_services_table.php`
- `database/migrations/2025_12_13_000004_add_dispute_id_to_settleable_tables.php`
- `app/Enums/DisputeStatus.php`
- `app/Enums/DisputeResolution.php`
- `app/Enums/DisputeVoteOutcome.php`
- `app/Models/Dispute.php`
- `app/Models/DisputeVote.php`
- `app/Models/Traits/Disputable.php`
- `app/Events/DisputeCreated.php`
- `app/Events/DisputeResolved.php`
- `app/Events/DisputeVoteReceived.php`
- `app/Listeners/SendDisputeNotification.php`
- `app/Listeners/SendDisputeResolutionNotification.php`
- `app/Listeners/CheckDisputeThreshold.php`
- `app/Http/Controllers/DisputeController.php`
- `resources/js/Pages/Dispute/Show.vue`
- `resources/js/Components/DisputeButton.vue`
- `resources/js/Components/DisputeStatusBadge.vue`
- `app/Console/Commands/ExpireDisputes.php`
- `app/Console/Commands/SendDisputeReminders.php`
- `app/Callbacks/Handlers/DisputeVoteCallbackHandler.php`
- `app/Callbacks/Handlers/DisputeViewCallbackHandler.php`
- `app/Listeners/HandleDisputeParticipantLeft.php`
- `database/factories/DisputeFactory.php`
- `database/factories/DisputeVoteFactory.php`
- `tests/Unit/Services/DisputeServiceTest.php`
- `tests/Unit/Services/DisputePenaltyCalculationTest.php`
- `tests/Feature/DisputeCreationTest.php`
- `tests/Feature/DisputeVotingFlowTest.php`
- `tests/Feature/DisputeResolutionPathsTest.php`

### Modified Files
- `app/Enums/TransactionType.php` (added dispute transaction types)
- `app/Models/MessengerService.php` (added fraud tracking methods)
- `app/Models/Wager.php` (added Disputable trait)
- `app/Models/Challenge.php` (added Disputable trait + elimination-specific dispute overrides)
- `app/Services/DisputeService.php` (added createEliminationDispute, updated correctOutcome for eliminations)
- `app/Services/PointService.php` (added deductPercentage method)
- `app/Services/WagerService.php` (added reversal methods)
- `app/Services/ChallengeService.php` (added reversal methods)
- `app/Services/EliminationChallengeService.php` (added forceElimination method)
- `app/Providers/EventServiceProvider.php` (registered dispute events)
- `app/Services/MessageService.php` (added dispute message methods)
- `lang/en/messages.php` (added dispute message templates)
- `routes/web.php` (added dispute routes)
- `app/Http/Controllers/WagerController.php` (added dispute data to show response)
- `app/Http/Controllers/EliminationChallengeController.php` (added dispute data and survivors to show response)
- `resources/js/Pages/Wager/Show.vue` (integrated DisputeButton and DisputeStatusBadge)
- `resources/js/Pages/Elimination/Show.vue` (integrated DisputeButton and DisputeStatusBadge)
- `database/migrations/2025_12_13_000001_create_disputes_table.php` (added reminder_sent_at)
- `app/Models/Dispute.php` (added reminder_sent_at field)
- `routes/console.php` (registered dispute commands in scheduler)
- `app/Providers/AppServiceProvider.php` (registered dispute callback handlers)
- `app/Http/Controllers/Api/TelegramWebhookController.php` (added HandleDisputeParticipantLeft hook in removeUserFromGroup)
