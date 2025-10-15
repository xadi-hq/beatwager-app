<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Group;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconcilePoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:reconcile
                            {--group= : Specific group ID to reconcile (optional)}
                            {--fix : Auto-fix discrepancies (default is report-only)}
                            {--threshold=10 : Alert threshold for discrepancies in points}
                            {--dry-run : Preview changes without applying them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect and optionally fix point balance discrepancies between cached aggregates and transaction ledger';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Starting Point Reconciliation...');
        $this->newLine();

        $groupId = $this->option('group');
        $fix = $this->option('fix');
        $dryRun = $this->option('dry-run');
        $threshold = (int) $this->option('threshold');

        // Validate conflicting options
        if ($fix && $dryRun) {
            $this->error('Cannot use --fix and --dry-run together. Choose one.');
            return self::FAILURE;
        }

        // Determine mode
        $mode = $fix ? 'FIX' : ($dryRun ? 'DRY-RUN' : 'REPORT');
        $this->info("Mode: {$mode}");
        $this->info("Alert Threshold: {$threshold} points");
        $this->newLine();

        // Get groups to process
        $groups = $groupId
            ? Group::where('id', $groupId)->get()
            : Group::all();

        if ($groups->isEmpty()) {
            $this->warn('No groups found to process.');
            return self::SUCCESS;
        }

        $totalDiscrepancies = 0;
        $criticalDiscrepancies = 0;
        $fixedCount = 0;

        foreach ($groups as $group) {
            $this->info("Processing Group: {$group->telegram_chat_title} (ID: {$group->id})");

            $discrepancies = $this->findDiscrepancies($group->id);

            if (empty($discrepancies)) {
                $this->info('✅ No discrepancies found');
                $this->newLine();
                continue;
            }

            $totalDiscrepancies += count($discrepancies);

            // Display discrepancies
            $this->warn("⚠️  Found " . count($discrepancies) . " discrepancies:");
            $this->newLine();

            foreach ($discrepancies as $discrepancy) {
                $actualBalance = $discrepancy->actual_balance ?? 0;
                $earnedDiff = abs($discrepancy->points_earned - $discrepancy->actual_earned);
                $spentDiff = abs($discrepancy->points_spent - $discrepancy->actual_spent);
                $balanceDiff = abs($discrepancy->points - $actualBalance);

                $isCritical = $earnedDiff >= $threshold || $spentDiff >= $threshold || $balanceDiff >= $threshold;

                if ($isCritical) {
                    $criticalDiscrepancies++;
                }

                $icon = $isCritical ? '🚨' : '⚠️ ';

                $this->line("{$icon} User ID: {$discrepancy->user_id}");
                $this->line("   Cached Balance: {$discrepancy->points} | Actual: {$actualBalance} | Diff: " . ($discrepancy->points - $actualBalance));
                $this->line("   Cached Earned: {$discrepancy->points_earned} | Actual: {$discrepancy->actual_earned} | Diff: " . ($discrepancy->points_earned - $discrepancy->actual_earned));
                $this->line("   Cached Spent: {$discrepancy->points_spent} | Actual: {$discrepancy->actual_spent} | Diff: " . ($discrepancy->points_spent - $discrepancy->actual_spent));

                if ($fix && !$dryRun) {
                    $this->fixDiscrepancy($discrepancy);
                    $fixedCount++;
                    $this->info("   ✅ Fixed!");
                } elseif ($dryRun) {
                    $this->comment("   [DRY-RUN] Would fix to: Balance={$actualBalance}, Earned={$discrepancy->actual_earned}, Spent={$discrepancy->actual_spent}");
                }

                $this->newLine();
            }

            // Log discrepancies
            $this->logDiscrepancies($group, $discrepancies, $mode);
        }

        // Summary
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('Summary:');
        $this->info("Total Discrepancies: {$totalDiscrepancies}");
        $this->info("Critical (≥{$threshold}pts): {$criticalDiscrepancies}");

        if ($fix && !$dryRun) {
            $this->info("Fixed: {$fixedCount}");
        }

        $this->info('═══════════════════════════════════════');

        // Alert if critical discrepancies found
        if ($criticalDiscrepancies > 0) {
            $this->error("🚨 {$criticalDiscrepancies} critical discrepancies found (≥{$threshold} points)!");

            if (!$fix) {
                $this->comment('Run with --fix to automatically correct them, or --dry-run to preview changes.');
            }
        } elseif ($totalDiscrepancies > 0) {
            $this->warn('Minor discrepancies found but below alert threshold.');
        } else {
            $this->info('✅ All point balances are synchronized!');
        }

        return self::SUCCESS;
    }

    /**
     * Find discrepancies between cached and actual values
     */
    private function findDiscrepancies(string $groupId)
    {
        $query = "
            SELECT
                gu.user_id,
                gu.group_id,
                gu.points,
                gu.points_earned,
                gu.points_spent,
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
        ";

        return DB::select($query, [$groupId]);
    }

    /**
     * Fix a discrepancy by updating cached values to match actual
     */
    private function fixDiscrepancy($discrepancy): void
    {
        DB::table('group_user')
            ->where('user_id', $discrepancy->user_id)
            ->where('group_id', $discrepancy->group_id)
            ->update([
                'points' => $discrepancy->actual_balance ?? 0,
                'points_earned' => $discrepancy->actual_earned,
                'points_spent' => $discrepancy->actual_spent,
                'updated_at' => now(),
            ]);
    }

    /**
     * Log discrepancies to dedicated log file
     */
    private function logDiscrepancies($group, array $discrepancies, string $mode): void
    {
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'group_id' => $group->id,
            'group_name' => $group->telegram_chat_title,
            'mode' => $mode,
            'discrepancy_count' => count($discrepancies),
            'discrepancies' => array_map(function ($d) {
                return [
                    'user_id' => $d->user_id,
                    'cached' => [
                        'balance' => $d->points,
                        'earned' => $d->points_earned,
                        'spent' => $d->points_spent,
                    ],
                    'actual' => [
                        'balance' => $d->actual_balance ?? 0,
                        'earned' => $d->actual_earned,
                        'spent' => $d->actual_spent,
                    ],
                    'diff' => [
                        'balance' => $d->points - ($d->actual_balance ?? 0),
                        'earned' => $d->points_earned - $d->actual_earned,
                        'spent' => $d->points_spent - $d->actual_spent,
                    ],
                ];
            }, $discrepancies),
        ];

        Log::channel('daily')->info('Point Reconciliation Report', $logData);
    }
}
