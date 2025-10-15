# Flexible Binary Wagers

## Overview

Binary wagers now support customizable labels and threshold-based auto-settlement, enabling a wide variety of prediction types beyond simple Yes/No questions.

## Features

### 1. Custom Labels

Binary wagers can use any two labels instead of the default "Yes"/"No":

```php
// Examples:
'label_option_a' => 'Over'
'label_option_b' => 'Under'

'label_option_a' => 'Before'
'label_option_b' => 'After'

'label_option_a' => 'Male'
'label_option_b' => 'Female'

'label_option_a' => 'Red'
'label_option_b' => 'Blue'
```

### 2. Threshold-Based Auto-Settlement

#### Over/Under (Numeric Threshold)

For predictions involving numeric values:

```php
'type' => 'binary',
'label_option_a' => 'Over',
'label_option_b' => 'Under',
'threshold_value' => 100000.00,  // e.g., $100k
```

**Settlement Logic:**
- Actual value >= threshold → "Over" (option_a / yes) wins
- Actual value < threshold → "Under" (option_b / no) wins

**Use Cases:**
- "Will Bitcoin reach $100k by EOY?"
- "Will the movie gross over $500M?"
- "Will we get more than 50 signups?"

#### Before/After (Date Threshold)

For predictions involving dates:

```php
'type' => 'binary',
'label_option_a' => 'Before',
'label_option_b' => 'After',
'threshold_date' => '2026-01-01',
```

**Settlement Logic:**
- Actual date < threshold → "Before" (option_a / yes) wins
- Actual date >= threshold → "After" (option_b / no) wins

**Use Cases:**
- "Will Ukraine war end before 2026?"
- "Will product launch before Q3?"
- "Will election happen before summer?"

### 3. Manual Settlement Binary

Binary wagers without thresholds use manual settlement:

```php
'type' => 'binary',
'label_option_a' => 'Male',
'label_option_b' => 'Female',
// No threshold fields
```

## Database Schema

### New Columns (Migration: 2025_10_29_000000)

```php
$table->string('label_option_a')->default('Yes');  // Custom label for option A
$table->string('label_option_b')->default('No');   // Custom label for option B
$table->decimal('threshold_value', 15, 2)->nullable();  // Numeric threshold
$table->date('threshold_date')->nullable();        // Date threshold
```

## API Usage

### Creating a Wager

**Endpoint:** `POST /wager`

**Over/Under Example:**
```json
{
  "title": "Will Bitcoin reach $100k by EOY?",
  "type": "binary",
  "label_option_a": "Over",
  "label_option_b": "Under",
  "threshold_value": 100000,
  "stake_amount": 100,
  "betting_closes_at": "2025-12-31T23:59:59",
  "group_id": "..."
}
```

**Before/After Example:**
```json
{
  "title": "Will Ukraine war end before 2026?",
  "type": "binary",
  "label_option_a": "Before",
  "label_option_b": "After",
  "threshold_date": "2026-01-01",
  "stake_amount": 50,
  "betting_closes_at": "2025-12-31T23:59:59",
  "group_id": "..."
}
```

**Custom Binary Example:**
```json
{
  "title": "Will next president be male or female?",
  "type": "binary",
  "label_option_a": "Male",
  "label_option_b": "Female",
  "stake_amount": 75,
  "betting_closes_at": "2026-11-03T00:00:00",
  "group_id": "..."
}
```

### Joining a Wager

Users join by selecting either option (always stored as 'yes' or 'no' internally):

```php
// Telegram callback format:
"wager:{wager_id}:yes"  // Selects option_a (Over/Before/Male/etc)
"wager:{wager_id}:no"   // Selects option_b (Under/After/Female/etc)
```

### Settlement

#### Auto-Settlement (Threshold-Based)

For wagers with thresholds, use the model's helper method:

```php
$wager = Wager::find($wagerId);
$actualValue = getActualValue();  // From data source

// Automatically determines 'yes' or 'no' based on threshold
$outcome = $wager->determineThresholdOutcome($actualValue);

$wagerService->settleWager($wager, $outcome, "Bitcoin reached $105k");
```

#### Manual Settlement

For wagers without thresholds, admin selects outcome:

```php
// User clicks settlement button
$wagerService->settleWager($wager, 'yes', "Next president is male");
// or
$wagerService->settleWager($wager, 'no', "Next president is female");
```

## Model Methods

### `Wager::hasAutoSettlement()`

Check if wager can use auto-settlement:

```php
if ($wager->hasAutoSettlement()) {
    // Wager has threshold_value or threshold_date
    // Can auto-calculate outcome
}
```

### `Wager::determineThresholdOutcome($actualValue)`

Calculate outcome based on threshold:

```php
// For numeric threshold (over/under)
$outcome = $wager->determineThresholdOutcome(105000);  // 'yes' if >= threshold

// For date threshold (before/after)
$outcome = $wager->determineThresholdOutcome('2025-06-15');  // 'yes' if < threshold
```

### `Wager::getDisplayOptions()`

Returns display labels:

```php
$wager->getDisplayOptions();
// Returns: ['Over', 'Under'] or ['Before', 'After'] or ['Yes', 'No']
```

## Button Rendering

Telegram buttons automatically use custom labels:

```php
// MessageService::buildWagerButtons()
'binary' => [
    new Button(
        label: $wager->label_option_a ?? 'Yes',  // "Over", "Before", etc.
        value: "wager:{$wager->id}:yes"
    ),
    new Button(
        label: $wager->label_option_b ?? 'No',   // "Under", "After", etc.
        value: "wager:{$wager->id}:no"
    ),
]
```

## Edge Cases

### Exact Threshold Values

- **Over/Under:** Exact match counts as "over" (>=)
- **Before/After:** Exact date counts as "after" (>=)

```php
// Example: threshold_value = 100
$wager->determineThresholdOutcome(100);   // Returns 'yes' (over)
$wager->determineThresholdOutcome(99);    // Returns 'no' (under)

// Example: threshold_date = '2026-01-01'
$wager->determineThresholdOutcome('2026-01-01');  // Returns 'no' (after)
$wager->determineThresholdOutcome('2025-12-31');  // Returns 'yes' (before)
```

### Validation

Labels are validated on creation:

```php
'label_option_a' => 'nullable|string|max:50',
'label_option_b' => 'nullable|string|max:50',
'threshold_value' => 'nullable|numeric',
'threshold_date' => 'nullable|date',
```

## Migration

Run the migration to add new columns:

```bash
php artisan migrate
```

Existing binary wagers will use default labels ("Yes"/"No") and have null thresholds (manual settlement).

## Testing Scenarios

1. **Over/Under:** Bitcoin $100k threshold with actual $105k → "Over" wins
2. **Before/After:** 2026 date threshold with actual 2025-06-15 → "Before" wins
3. **Custom Labels:** Male/Female with manual settlement → Admin selects outcome
4. **Edge Cases:** Exact threshold value/date handling
5. **Backwards Compatibility:** Existing Yes/No wagers continue to work

## Future Enhancements

### Phase 2: Top N Ranking

Landing page implementation for complex predictions:
- Elections: "Predict top 3 parties"
- Sports: "Predict podium finish"
- Competitions: "Predict winner and runner-up"

Stay tuned for landing page authentication flow and ranking UI!
