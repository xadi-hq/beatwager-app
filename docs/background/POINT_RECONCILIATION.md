# Point Reconciliation System

**Status**: Phase 1 Complete (October 22, 2025)
**Command**: `php artisan points:reconcile`
**Schedule**: Weekly (Mondays at 3am)

## Overview

The Point Reconciliation system is a data integrity monitoring tool that detects discrepancies between cached point aggregates (`group_user` table) and the authoritative transaction ledger (`transactions` table).

## Problem Statement

The application maintains a dual source of truth for point balances:
- **Cached Aggregates** (performance): `group_user.points`, `points_earned`, `points_spent`
- **Transaction Ledger** (authority): `transactions` table with complete history

Without verification, these can drift due to:
- Manual database updates
- Race conditions
- Season operations
- Migration errors
- Logic bugs

## Phase 1: Detection & Monitoring

### Command Usage

**Report Mode (Default)**
```bash
php artisan points:reconcile
```
Detects and logs discrepancies without making changes.

**Dry-Run Mode**
```bash
php artisan points:reconcile --dry-run
```
Shows what would be fixed without applying changes.

**Auto-Fix Mode**
```bash
php artisan points:reconcile --fix
```
Automatically corrects discrepancies by updating cached values to match transaction ledger.

**Custom Threshold**
```bash
php artisan points:reconcile --threshold=50
```
Changes the alert threshold (default: 10 points).

**Single Group**
```bash
php artisan points:reconcile --group=<group-id>
```
Process only a specific group.

### How It Works

1. **Detection Query** ([ReconcilePoints.php:155](../app/Console/Commands/ReconcilePoints.php#L155))
   ```sql
   SELECT
       gu.user_id, gu.group_id,
       gu.points, gu.points_earned, gu.points_spent,
       COALESCE(SUM(CASE WHEN t.amount > 0 THEN t.amount ELSE 0 END), 0) as actual_earned,
       COALESCE(SUM(CASE WHEN t.amount < 0 THEN ABS(t.amount) ELSE 0 END), 0) as actual_spent,
       (SELECT balance_after FROM transactions WHERE user_id = gu.user_id AND group_id = gu.group_id ORDER BY created_at DESC LIMIT 1) as actual_balance
   FROM group_user gu
   LEFT JOIN transactions t ON t.user_id = gu.user_id AND t.group_id = gu.group_id
   WHERE gu.group_id = ?
   GROUP BY gu.user_id, gu.group_id, gu.points, gu.points_earned, gu.points_spent
   HAVING
       gu.points_earned != COALESCE(SUM(CASE WHEN t.amount > 0 THEN t.amount ELSE 0 END), 0)
       OR gu.points_spent != COALESCE(SUM(CASE WHEN t.amount < 0 THEN ABS(t.amount) ELSE 0 END), 0)
       OR gu.points != COALESCE((SELECT balance_after FROM transactions WHERE user_id = gu.user_id AND group_id = gu.group_id ORDER BY created_at DESC LIMIT 1), 0)
   ```

2. **Logging** ([ReconcilePoints.php:198](../app/Console/Commands/ReconcilePoints.php#L198))
   - All discrepancies logged to Laravel daily log
   - Includes: group_id, user_id, cached values, actual values, differences
   - Mode tracked: REPORT, DRY-RUN, or FIX

3. **Auto-Fix** ([ReconcilePoints.php:179](../app/Console/Commands/ReconcilePoints.php#L179))
   ```php
   DB::table('group_user')
       ->where('user_id', $discrepancy->user_id)
       ->where('group_id', $discrepancy->group_id)
       ->update([
           'points' => $discrepancy->actual_balance ?? 0,
           'points_earned' => $discrepancy->actual_earned,
           'points_spent' => $discrepancy->actual_spent,
       ]);
   ```

4. **Scheduling** ([console.php:75](../routes/console.php#L75))
   ```php
   Schedule::command('points:reconcile')
       ->weeklyOn(1, '03:00')  // Every Monday at 3am
       ->withoutOverlapping()
       ->onOneServer();
   ```

### Output Example

```
🔍 Starting Point Reconciliation...

Mode: REPORT
Alert Threshold: 10 points

Processing Group: My Group (ID: 019a0842-ae55-7004-a064-7ee9c9770a68)
⚠️  Found 2 discrepancies:

🚨 User ID: 019a0842-b221-7339-9a37-5b2044f23842
   Cached Balance: 1050 | Actual: 1065 | Diff: -15
   Cached Earned: 1100 | Actual: 1115 | Diff: -15
   Cached Spent: 50 | Actual: 50 | Diff: 0

⚠️  User ID: 019a0842-c331-8449-b148-6d5152f87923
   Cached Balance: 995 | Actual: 1000 | Diff: -5
   Cached Earned: 1000 | Actual: 1005 | Diff: -5
   Cached Spent: 5 | Actual: 5 | Diff: 0

═══════════════════════════════════════
Summary:
Total Discrepancies: 2
Critical (≥10pts): 1
═══════════════════════════════════════
🚨 1 critical discrepancies found (≥10 points)!
Run with --fix to automatically correct them, or --dry-run to preview changes.
```

### Initial Run Results

**Date**: October 22, 2025
**Groups Processed**: 35
**Discrepancies Found**: 0
**Status**: ✅ All point balances synchronized

## Phase 2: Evaluation (Pending)

After 2-4 weeks of monitoring:
- Review reconciliation logs
- Assess frequency and severity of drift
- **If drift is rare/never**: Current approach acceptable with monitoring
- **If drift is frequent**: Proceed to Phase 3

## Phase 3: Single Source of Truth (Conditional)

Only implement if Phase 2 reveals frequent discrepancies:
- Remove cached fields from `group_user` table
- Calculate from transactions on-the-fly
- Add indexes: `(user_id, group_id, created_at)`, `(user_id, group_id, amount)`
- Database views or computed columns for performance
- Refactor code to use transaction-based calculations

## Testing

All point-related tests pass:
```bash
docker exec beatwager-app php artisan test --filter=Point
# 32 tests, 79 assertions - all passing
```

## Maintenance

- **Weekly Monitoring**: Check logs every Monday after 3am run
- **Alert Threshold**: Adjust via `--threshold` if needed
- **Manual Runs**: Can be triggered anytime for spot checks
- **Log Location**: `storage/logs/laravel-YYYY-MM-DD.log`
- **Search Logs**: `grep "Point Reconciliation Report" storage/logs/*.log`

## Related Files

- [ReconcilePoints.php](../app/Console/Commands/ReconcilePoints.php) - Main command
- [console.php:75](../routes/console.php#L75) - Scheduler configuration
- [PointService.php](../app/Services/PointService.php) - Point transaction logic
- [TODO.md](./TODO.md) - Phase tracking and next steps
