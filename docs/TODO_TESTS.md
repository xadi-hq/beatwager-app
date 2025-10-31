# Test Coverage Analysis & TODO

**Generated:** 2025-10-31
**Test Framework:** Pest (Laravel)
**Last Run:** 323 passed, 8 failed, 1 risky, 10 skipped

---

## Current Test Suite Status

### Summary
- **Total source files:** 147 PHP files
- **Total test files:** 36 test files
- **Test-to-source ratio:** ~24.5%
- **Duration:** ~60 seconds

### Results Breakdown
- ✅ **323 tests passed** (858 assertions)
- ❌ **8 tests failed** (all in ControllerSecurityTest)
- ⚠️ **1 test risky**
- ⏭️ **10 tests skipped**

---

## Test Organization

### Feature Tests (20 files - Integration/E2E)
Located in `tests/Feature/`

- ✅ `ChallengeTwoTypesTest.php`
- ✅ `CheckGroupActivityTest.php`
- ✅ `CheckSeasonEndingsTest.php`
- ✅ `CleanupExpiredItemsTest.php`
- ❌ `ControllerSecurityTest.php` - **8 failures**
- ✅ `DonationFlowTest.php`
- ✅ `EdgeCasesTest.php`
- ✅ `GrudgeServiceTest.php`
- ✅ `LeaderboardCommandTest.php`
- ✅ `MessageTrackingServiceTest.php`
- ✅ `MessagingSystemTest.php`
- ✅ `PointDecayTest.php`
- ✅ `PointEconomyTest.php`
- ✅ `SendEngagementPromptsTest.php`
- ✅ `SettleCommandTest.php`
- ✅ `SettlementReminderTest.php`
- ✅ `SignedUrlAuthenticationTest.php`
- ✅ `TransactionWagerRelationshipTest.php`
- ✅ `WagerCreationFlowTest.php`
- ✅ `WagerSettlementTest.php`

### Unit Tests (16 files - Isolated components)
Located in `tests/Unit/`

**Commands:**
- ✅ `Commands/CommandRegistryTest.php`

**DTOs:**
- ✅ `DTOs/MessageFormattingTest.php`

**Jobs:**
- ✅ `Jobs/SendSeasonMilestoneDropsTest.php`

**Messengers:**
- ✅ `Messengers/TelegramMessengerButtonTest.php`
- ✅ `Messengers/TelegramMessengerFormattingTest.php`

**Models:**
- ✅ `Models/GroupEventAttendanceTest.php`
- ✅ `Models/GroupEventRsvpTest.php`
- ✅ `Models/GroupEventTest.php`
- ✅ `Models/ScheduledMessageDropsTest.php`
- ✅ `Models/WagerModelLogicTest.php`

**Services:**
- ✅ `Services/EventServiceTest.php`
- ✅ `Services/LLMServiceTest.php`
- ✅ `Services/PointServiceDecayTest.php`
- ✅ `Services/ScheduledMessageServiceTest.php`
- ✅ `Services/WagerServiceTest.php`

---

## Coverage Gaps by Component

### 🔴 Controllers (25% coverage - 3/12 tested)

#### ✅ Tested Controllers
- `DonationController.php` - Full coverage
- `WagerController.php` - Partial (security tests failing)
- `EventController.php` - Partial (security tests failing)

#### ❌ Missing Controller Tests (High Priority)
- **`Api/TelegramWebhookController.php`** - 🚨 **CRITICAL** - Main entry point
- `ChallengeController.php` - Challenge feature completely untested
- `GroupController.php` - Group management
- `GroupSettingsController.php` - Settings management
- `GroupStreakController.php` - Streak tracking
- `ScheduledMessageController.php` - Message scheduling
- `SeasonController.php` - Season management
- `ShortUrlController.php` - URL shortening
- `DashboardController.php` - Dashboard views

---

### 🔴 Services (46% coverage - 6/13 tested)

#### ✅ Tested Services
- `EventService.php` - Unit tests
- `GrudgeService.php` - Feature tests
- `LLMService.php` - Unit tests
- `MessageTrackingService.php` - Feature tests
- `PointService.php` - Unit tests (decay)
- `WagerService.php` - Unit tests

#### ❌ Missing Service Tests (High Priority)
- **`ChallengeService.php`** - 🚨 **CRITICAL** - Core challenge logic
- **`SeasonService.php`** - 🚨 **CRITICAL** - Season management
- **`MessengerFactory.php`** - Message routing logic
- **`ContentGenerator.php`** - LLM content generation
- `EngagementTriggerService.php` - Engagement detection
- `MessageService.php` - Partial coverage only
- `ScheduledMessageService.php` - Has unit test but needs more

---

### 🔴 Command Handlers (6% coverage - 1/18 tested)

#### ✅ Tested Handlers
- `LeaderboardCommandHandler.php` - Full coverage

#### ❌ Missing Command Handler Tests (High Priority)
**Core Commands:**
- `NewWagerCommandHandler.php` - Create wagers
- `NewChallengeCommandHandler.php` - Create challenges
- `NewEventCommandHandler.php` - Create events
- `SettleCommandHandler.php` - Settle wagers
- `JoinCommandHandler.php` - Join wagers

**User Commands:**
- `BalanceCommandHandler.php` - Check balance
- `MyWagersCommandHandler.php` - View user wagers
- `WagersCommandHandler.php` - List wagers
- `ChallengesCommandHandler.php` - List challenges
- `EventsCommandHandler.php` - List events
- `DonateCommandHandler.php` - Donate points

**Utility Commands:**
- `StartCommandHandler.php` - Bot startup
- `HelpCommandHandler.php` - Help text
- `LoginCommandHandler.php` - Authentication
- `UnknownCommandHandler.php` - Error handling

---

### 🔴 Callback Handlers (0% coverage - 0/11 tested)

#### ❌ All Missing Tests (Medium Priority)
**Wager Callbacks:**
- `WagerCallbackHandler.php`
- `ViewWagerCallbackHandler.php`
- `SettleWagerCallbackHandler.php`
- `TrackWagerProgressCallbackHandler.php`

**Challenge Callbacks:**
- `ChallengeViewCallbackHandler.php`
- `ChallengeAcceptCallbackHandler.php`
- `SettleChallengeCallbackHandler.php`
- `TrackChallengeProgressCallbackHandler.php`

**Event Callbacks:**
- `EventRsvpCallbackHandler.php`
- `ViewEventDetailsCallbackHandler.php`
- `TrackEventProgressCallbackHandler.php`

---

### 🟡 Jobs (17% coverage - 1/6 tested)

#### ✅ Tested Jobs
- `SendSeasonMilestoneDrops.php` - Unit tests

#### ❌ Missing Job Tests (Medium Priority)
- **`ApplyPointDecay.php`** - 🚨 **CRITICAL** - Point economy stability
- `SendBirthdayReminders.php` - Birthday notifications
- `SendEngagementPrompts.php` - User engagement
- `SendEventAttendancePrompts.php` - Event reminders
- `SendEventRsvpReminders.php` - RSVP reminders

---

### 🟡 Models (33% coverage - 5/15 tested)

#### ✅ Tested Models
- `GroupEvent.php` - Full coverage
- `GroupEventRsvp.php` - Full coverage
- `GroupEventAttendance.php` - Full coverage
- `Wager.php` - Model logic tested
- `ScheduledMessage.php` - Tested

#### ❌ Missing Model Tests (Medium Priority)
**Core Models:**
- `Challenge.php` - Challenge domain logic
- `Group.php` - Group relationships
- `User.php` - User relationships
- `Transaction.php` - Financial logic
- `WagerEntry.php` - Entry relationships

**Supporting Models:**
- `GroupSeason.php` - Season logic
- `EventStreakConfig.php` - Streak configuration
- `WagerSettlementToken.php` - Token logic
- `AuditEvent.php` - Audit relationships
- `SentMessage.php` - Message tracking

---

## 🚨 Critical Failing Tests

### ControllerSecurityTest.php (8 failures)

All failures in `tests/Feature/ControllerSecurityTest.php` - **MUST FIX IMMEDIATELY**

#### 1. CSRF/Signed URL Authentication Issues
**Issue:** Expecting 401 Unauthorized, getting 419 CSRF Token Mismatch

```php
// tests/Feature/ControllerSecurityTest.php:34
it('requires authentication for wager settlement', function () {
    // Unauthenticated users get 401 from signed.auth middleware
    $response->assertStatus(401); // ❌ Getting 419 instead
});

// tests/Feature/ControllerSecurityTest.php:145
it('requires authentication for event attendance', function () {
    // Unauthenticated users get 401 from signed.auth middleware
    $response->assertStatus(401); // ❌ Getting 419 instead
});
```

**Root Cause:** `AuthenticateFromSignedUrl` middleware not handling unauthenticated requests properly.

#### 2. Authorization Bypass - Wager Settlement
**Issue:** Users can settle other users' wagers (expecting 403, getting 200)

```php
// tests/Feature/ControllerSecurityTest.php:68
it('prevents user from settling another user\'s wager', function () {
    // Other user tries to settle
    $this->actingAs($otherUser)
        ->get('/wager/settle?token=' . $token->token)
        ->assertForbidden(); // ❌ Getting 200 OK - SECURITY ISSUE
});
```

**Root Cause:** Missing authorization check in `WagerController::settle()` - allows any authenticated user to settle any wager.

#### 3. Server Error on Unauthorized Access
**Issue:** Expecting 403 Forbidden, getting 500 Server Error

```php
// tests/Feature/ControllerSecurityTest.php:114
it('prevents placing bet on another user\'s wager without permission', function () {
    if ($response->status() !== 419) {
        $response->assertForbidden(); // ❌ Getting 500 instead
    }
});
```

**Root Cause:** Exception thrown instead of proper HTTP response.

#### 4. Form Validation Errors
**Issue:** Validation failures causing 302 redirects instead of expected responses

```php
// tests/Feature/ControllerSecurityTest.php:173
it('allows recording attendance with proper authorization', function () {
    if ($response->status() !== 419) {
        $response->assertOk(); // ❌ Getting 302 redirect
    }
});
// Error: "The attendee ids field is required."

// tests/Feature/ControllerSecurityTest.php:216
it('prevents unauthorized event updates', function () {
    if ($response->status() !== 419) {
        $response->assertForbidden(); // ❌ Getting 302 redirect
    }
});
```

**Root Cause:** Form validation expects different field names or session handling issues in test environment.

#### 5. Rate Limiting Not Working
**Issue:** Rate limiter blocking ALL requests (0 successful)

```php
// tests/Feature/ControllerSecurityTest.php:421
it('rate limits excessive requests', function () {
    // Make 100 requests, some should succeed
    $this->assertGreaterThan(0, $successfulRequests); // ❌ 0 requests succeeded
});
```

**Root Cause:** Rate limiter configuration too aggressive or not properly reset between tests.

---

## Recommended Test Implementation Priority

### 🔴 Phase 1: Security & Critical Bugs (IMMEDIATE)

1. **Fix ControllerSecurityTest failures** (est. 4-6 hours)
   - Fix authorization bypass in wager settlement
   - Fix CSRF/signed URL authentication
   - Fix server errors on unauthorized access
   - Fix form validation issues
   - Fix rate limiting test

2. **Test TelegramWebhookController** (est. 3-4 hours)
   - Main entry point for all user interactions
   - Create `tests/Feature/TelegramWebhookTest.php`

3. **Test ChallengeService** (est. 2-3 hours)
   - Core challenge logic untested
   - Create `tests/Unit/Services/ChallengeServiceTest.php`

### 🟡 Phase 2: Core Functionality (HIGH PRIORITY)

4. **Command Handler Tests** (est. 8-12 hours)
   - Create test file per handler
   - Start with: NewWager, NewChallenge, NewEvent, Settle, Join

5. **Controller Tests** (est. 6-8 hours)
   - ChallengeController
   - GroupController
   - SeasonController

6. **Job Tests** (est. 4-6 hours)
   - ApplyPointDecay (CRITICAL for economy)
   - Engagement/reminder jobs

### 🟢 Phase 3: Completeness (MEDIUM PRIORITY)

7. **Callback Handler Tests** (est. 6-8 hours)
   - All 11 callback handlers

8. **Model Tests** (est. 4-6 hours)
   - Challenge, Group, User, Transaction

9. **Service Completion** (est. 4-6 hours)
   - MessengerFactory, ContentGenerator, etc.

---

## Coverage Targets

| Component | Current | Target | Files to Add |
|-----------|---------|--------|--------------|
| Controllers | 25% | 80% | +7 test files |
| Services | 46% | 85% | +6 test files |
| Command Handlers | 6% | 70% | +12 test files |
| Callback Handlers | 0% | 60% | +7 test files |
| Jobs | 17% | 80% | +4 test files |
| Models | 33% | 70% | +7 test files |
| **Overall** | **~25%** | **~65%** | **+43 test files** |

---

## Test Quality Issues

### Deprecation Warnings
**Issue:** Pest metadata in doc-comments deprecated (PHPUnit 12 compatibility)

```php
// ❌ Old style (deprecated)
/**
 * @test
 */
public function it_does_something() { }

// ✅ New style (use Pest syntax)
it('does something', function () { });
```

**Action:** Already using Pest syntax, warnings likely from PHPUnit internals. Monitor for Pest updates.

### Risky Tests
**Issue:** 1 test marked as risky (no assertions)

**Action:** Review and add assertions to ensure test validity.

### Skipped Tests
**Issue:** 10 tests skipped

**Action:** Investigate why tests are skipped and fix underlying issues.

---

## Test Writing Guidelines

### Pest Test Structure
```php
<?php

use App\Models\User;
use App\Models\Group;

describe('Feature Name', function () {
    beforeEach(function () {
        // Setup for all tests in this group
        $this->user = User::factory()->create();
        $this->group = Group::factory()->create();
    });

    it('does something specific', function () {
        // Arrange
        $data = ['key' => 'value'];

        // Act
        $result = $this->user->doSomething($data);

        // Assert
        expect($result)->toBeTrue();
    });

    it('handles edge case', function () {
        // Test implementation
    })->throws(InvalidArgumentException::class);
});
```

### Controller Test Pattern
```php
it('returns 200 for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/api/resource')
        ->assertOk()
        ->assertJson(['success' => true]);
});
```

### Service Test Pattern
```php
it('creates resource with valid data', function () {
    $service = app(MyService::class);
    $data = ['title' => 'Test'];

    $result = $service->create($data);

    expect($result)->toBeInstanceOf(Resource::class);
    $this->assertDatabaseHas('resources', ['title' => 'Test']);
});
```

---

## Running Tests

### All Tests
```bash
make test
```

### Specific Test File
```bash
make test-filter FILTER=ControllerSecurityTest
```

### With Coverage
```bash
docker compose exec -T app vendor/bin/pest --coverage --min=0
```

### Watch Mode (Development)
```bash
docker compose exec app vendor/bin/pest --watch
```

---

## Next Steps

1. ✅ **Document test coverage** (COMPLETE)
2. 🔴 **Fix ControllerSecurityTest failures** (BLOCKED - security issues)
3. 🔴 **Add TelegramWebhookController tests** (CRITICAL)
4. 🔴 **Add ChallengeService tests** (CRITICAL)
5. 🟡 **Begin command handler test suite**

---

**Last Updated:** 2025-10-31
**Maintained by:** Development Team
**Review Frequency:** After each sprint
