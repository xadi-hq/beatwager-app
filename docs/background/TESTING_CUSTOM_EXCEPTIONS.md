# Testing Custom Exceptions - Quick Guide

**Run this after implementing custom exceptions**

---

## Step 1: Run Full Test Suite

```bash
docker compose exec app php artisan test
```

Expected: Most tests should pass. Some may need minor adjustments.

---

## Step 2: Update Failing Tests (if any)

### Common Pattern 1: Generic Exception Catch

**If you see:**
```
Test failed: Expected exception \Exception but got InsufficientPointsException
```

**Fix:**
```php
// Before
expect(fn() => $service->doSomething())
    ->toThrow(\Exception::class);

// After
use App\Exceptions\InsufficientPointsException;

expect(fn() => $service->doSomething())
    ->toThrow(InsufficientPointsException::class);
```

### Common Pattern 2: Exception Message Assertions

**If you see:**
```
Failed asserting that exception message contains "Insufficient points"
```

**Fix:**
```php
// Before
expect(fn() => $service->doSomething())
    ->toThrow(\Exception::class, 'Insufficient points');

// After - Option 1: Just check exception type
expect(fn() => $service->doSomething())
    ->toThrow(InsufficientPointsException::class);

// After - Option 2: Check user message
try {
    $service->doSomething();
} catch (InsufficientPointsException $e) {
    expect($e->getUserMessage())->toContain('don\'t have enough points');
}
```

---

## Step 3: Specific Tests to Check

### PointEconomyTest.php

**Test:** `it('prevents wagering with insufficient points')`

**Current:**
```php
expect(fn() => $wagerService->placeWager($wager, $user, 'yes', 100))
    ->toThrow(\Exception::class, 'Insufficient points');
```

**Update to:**
```php
use App\Exceptions\InsufficientPointsException;

expect(fn() => $wagerService->placeWager($wager, $user, 'yes', 100))
    ->toThrow(InsufficientPointsException::class);
```

### EdgeCasesTest.php

**Review all exception assertions** and update to specific exception types.

---

## Step 4: Manual Testing Scenarios

### Scenario 1: Insufficient Points

1. Create a wager with 100 point stake
2. Try to join with user who has only 50 points
3. **Expected:** Clear error message: "You don't have enough points. You need 100 but only have 50."

### Scenario 2: Join Closed Wager

1. Create and settle a wager
2. Try to join the settled wager
3. **Expected:** "This wager has already been settled."

### Scenario 3: Invalid Answer

**Binary Wager:**
1. Try to submit "maybe" as answer
2. **Expected:** "Binary answer must be 'yes' or 'no'."

**Numeric Wager:**
1. Wager with range 1-100
2. Try to submit "150"
3. **Expected:** "Answer must be a valid integer (at least 1 and at most 100)."

### Scenario 4: Duplicate Join

1. Join a wager successfully
2. Try to join the same wager again
3. **Expected:** "You've already placed a bet on this wager."

---

## Step 5: Controller Error Handling (Optional)

If you want better error messages in your controllers:

```php
// app/Http/Controllers/WagerController.php

use App\Exceptions\{
    InsufficientPointsException,
    InvalidAnswerException,
    InvalidStakeException,
    UserAlreadyJoinedException,
    WagerNotOpenException
};

public function join(Request $request, Wager $wager)
{
    try {
        $entry = $this->wagerService->placeWager(
            $wager,
            $request->user(),
            $request->input('answer'),
            $request->input('points')
        );

        return redirect()
            ->route('wager.show', $wager)
            ->with('success', 'Successfully joined wager!');

    } catch (InsufficientPointsException $e) {
        return back()->withErrors(['points' => $e->getUserMessage()]);

    } catch (WagerNotOpenException $e) {
        return back()->withErrors(['wager' => $e->getUserMessage()]);

    } catch (UserAlreadyJoinedException $e) {
        return back()->withErrors(['wager' => $e->getUserMessage()]);

    } catch (InvalidStakeException $e) {
        return back()->withErrors(['points' => $e->getUserMessage()]);

    } catch (InvalidAnswerException $e) {
        return back()->withErrors(['answer' => $e->getUserMessage()]);
    }
}
```

---

## Step 6: Global Exception Handler (Recommended)

Add to `app/Exceptions/Handler.php`:

```php
use App\Exceptions\BeatWagerException;

public function register(): void
{
    $this->renderable(function (BeatWagerException $e, Request $request) {
        // API requests
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $e->getUserMessage(),
                'error' => class_basename($e),
            ], $e->getStatusCode());
        }

        // Web requests
        return back()
            ->withErrors(['error' => $e->getUserMessage()])
            ->withInput();
    });
}
```

This handles ALL custom exceptions automatically!

---

## Quick Command Reference

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific test file
docker compose exec app php artisan test --filter=PointEconomyTest

# Run tests with coverage (if configured)
docker compose exec app php artisan test --coverage

# Clear application cache
docker compose exec app php artisan cache:clear

# Run code style fixer
docker compose exec app ./vendor/bin/pint
```

---

## Verification Checklist

- [ ] All tests pass
- [ ] Manual testing completed
- [ ] Error messages are user-friendly
- [ ] Controllers updated (optional but recommended)
- [ ] Global exception handler added (recommended)
- [ ] Documentation updated

---

## Rollback (if needed)

If something breaks and you need to rollback:

```bash
# Revert the service files
git checkout app/Services/WagerService.php
git checkout app/Services/PointService.php
git checkout app/Services/TokenService.php

# Remove exception files
rm -rf app/Exceptions/
```

But this shouldn't be necessary - the changes are backward compatible!

---

## Success Criteria

✅ All tests pass  
✅ Application runs without errors  
✅ Error messages are clear and helpful  
✅ No regressions in functionality  

---

## Need Help?

If tests fail or you encounter issues:

1. Check the exception message - it should tell you what's wrong
2. Review the specific test that's failing
3. Compare against the patterns in this guide
4. Check `docs/CUSTOM_EXCEPTIONS_IMPLEMENTATION.md` for detailed examples
