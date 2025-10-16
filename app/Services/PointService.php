<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientPointsException;
use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wager;
use App\Models\WagerEntry;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Get user's current point balance in a group
     */
    public function getBalance(User $user, Group $group): int
    {
        return $user->groups()
            ->where("group_id", $group->id)
            ->first()
            ?->pivot
            ?->points ?? 0;
    }

    /**
     * Deduct points from user's balance
     */
    public function deductPoints(
        User $user,
        Group $group,
        int $amount,
        string $type,
        ?Wager $wager = null,
        ?WagerEntry $wagerEntry = null
    ): Transaction {
        return DB::transaction(function () use ($user, $group, $amount, $type, $wager, $wagerEntry) {
            $balanceBefore = $this->getBalance($user, $group);

            if ($balanceBefore < $amount) {
                throw new InsufficientPointsException($amount, $balanceBefore);
            }

            $balanceAfter = $balanceBefore - $amount;

            // Update pivot table using raw query to avoid DB::raw casting issues
            DB::table("group_user")
                ->where("user_id", $user->id)
                ->where("group_id", $group->id)
                ->update([
                    "points" => $balanceAfter,
                    "points_spent" => DB::raw("points_spent + " . $amount),
                    "last_activity_at" => now(),
                ]);

            // Record transaction
            return Transaction::create([
                "user_id" => $user->id,
                "group_id" => $group->id,
                "type" => $type,
                "amount" => -$amount,
                "balance_before" => $balanceBefore,
                "balance_after" => $balanceAfter,
                "wager_id" => $wager?->id,
                "wager_entry_id" => $wagerEntry?->id,
            ]);
        });
    }

    /**
     * Award points to user's balance
     */
    public function awardPoints(
        User $user,
        Group $group,
        int $amount,
        string $type,
        ?Wager $wager = null,
        ?WagerEntry $wagerEntry = null
    ): Transaction {
        return DB::transaction(function () use ($user, $group, $amount, $type, $wager, $wagerEntry) {
            $balanceBefore = $this->getBalance($user, $group);
            $balanceAfter = $balanceBefore + $amount;

            // Update pivot table using raw query to avoid DB::raw casting issues
            DB::table("group_user")
                ->where("user_id", $user->id)
                ->where("group_id", $group->id)
                ->update([
                    "points" => $balanceAfter,
                    "points_earned" => DB::raw("points_earned + " . $amount),
                    "last_activity_at" => now(),
                ]);

            // Record transaction
            return Transaction::create([
                "user_id" => $user->id,
                "group_id" => $group->id,
                "type" => $type,
                "amount" => $amount,
                "balance_before" => $balanceBefore,
                "balance_after" => $balanceAfter,
                "wager_id" => $wager?->id,
                "wager_entry_id" => $wagerEntry?->id,
            ]);
        });
    }

    /**
     * Record a loss (points already deducted)
     */
    public function recordLoss(
        User $user,
        Group $group,
        int $amount,
        Wager $wager,
        WagerEntry $wagerEntry
    ): Transaction {
        $currentBalance = $this->getBalance($user, $group);

        return Transaction::create([
            "user_id" => $user->id,
            "group_id" => $group->id,
            "type" => "wager_lost",
            "amount" => 0, // Already deducted
            "balance_before" => $currentBalance,
            "balance_after" => $currentBalance,
            "wager_id" => $wager->id,
            "wager_entry_id" => $wagerEntry->id,
            "description" => "Lost wager: " . $wager->title,
        ]);
    }

    /**
     * Refund points to user
     */
    public function refundPoints(
        User $user,
        Group $group,
        int $amount,
        Wager $wager,
        WagerEntry $wagerEntry
    ): Transaction {
        return $this->awardPoints(
            $user,
            $group,
            $amount,
            "wager_refunded",
            $wager,
            $wagerEntry
        );
    }

    /**
     * Initialize user's points in a group
     */
    public function initializeUserPoints(User $user, Group $group): void
    {
        DB::transaction(function () use ($user, $group) {
            $startingBalance = $group->starting_balance;

            $user->groups()->attach($group->id, [
                "id" => \Illuminate\Support\Str::uuid(),
                "points" => $startingBalance,
                "points_earned" => 0,
                "points_spent" => 0,
                "role" => "participant",
            ]);

            Transaction::create([
                "user_id" => $user->id,
                "group_id" => $group->id,
                "type" => "initial_balance",
                "amount" => $startingBalance,
                "balance_before" => 0,
                "balance_after" => $startingBalance,
                "description" => "Initial balance for joining group",
            ]);
        });
    }

    /**
     * Apply point decay to inactive users
     * Legacy method - kept for backward compatibility
     */
    public function applyPointDecay(User $user, Group $group): ?Transaction
    {
        if (! $group->point_decay_enabled) {
            return null;
        }

        $pivot = $user->groups()->where("group_id", $group->id)->first()?->pivot;
        if (! $pivot) {
            return null;
        }

        $lastActivity = $pivot->last_activity_at ?? $pivot->created_at;
        $daysSinceActivity = now()->diffInDays($lastActivity);

        if ($daysSinceActivity < $group->point_decay_grace_days) {
            return null;
        }

        $currentBalance = $pivot->points;
        $decayAmount = (int) ($currentBalance * ($group->point_decay_rate / 100));

        if ($decayAmount === 0) {
            return null;
        }

        return $this->deductPoints(
            $user,
            $group,
            $decayAmount,
            "point_decay"
        );
    }

    /**
     * Calculate decay amount based on Phase 2 rules:
     * 5% of balance, min 50pts, max 100pts
     */
    public function calculateDecayAmount(int $currentPoints): int
    {
        $decayAmount = (int) ceil($currentPoints * 0.05); // 5%

        // Apply min/max bounds
        return max(50, min(100, $decayAmount));
    }

    /**
     * Check if user should receive decay warning (day 12 of inactivity)
     */
    public function shouldSendDecayWarning($userGroupPivot): bool
    {
        if (!$userGroupPivot->last_wager_joined_at) {
            // Never joined a wager, check from creation date
            $lastActivity = $userGroupPivot->created_at;
        } else {
            $lastActivity = $userGroupPivot->last_wager_joined_at;
        }

        // Parse the date string if it's not a Carbon instance
        if (is_string($lastActivity)) {
            $lastActivity = \Carbon\Carbon::parse($lastActivity);
        }

        $daysInactive = (int) $lastActivity->startOfDay()->diffInDays(now()->startOfDay());

        // Send warning on day 12, but only if not already sent recently
        if ($daysInactive === 12) {
            // Check if warning was already sent in last 24 hours
            if ($userGroupPivot->decay_warning_sent_at) {
                $warningTime = is_string($userGroupPivot->decay_warning_sent_at)
                    ? \Carbon\Carbon::parse($userGroupPivot->decay_warning_sent_at)
                    : $userGroupPivot->decay_warning_sent_at;
                $hoursSinceWarning = now()->diffInHours($warningTime);
                return $hoursSinceWarning >= 24;
            }
            return true;
        }

        return false;
    }

    /**
     * Apply point decay for inactive users in a group (Phase 2 implementation)
     * Returns array of results for logging
     */
    public function applyDecayForGroup(Group $group): array
    {
        $results = [
            'warnings_sent' => 0,
            'decay_applied' => 0,
            'total_processed' => 0,
        ];

        $userGroups = DB::table('group_user')
            ->where('group_id', $group->id)
            ->get();

        foreach ($userGroups as $pivot) {
            $results['total_processed']++;

            // Determine last activity
            $lastActivity = $pivot->last_wager_joined_at ?? $pivot->created_at;
            // Parse the date string to Carbon instance
            if (is_string($lastActivity)) {
                $lastActivity = \Carbon\Carbon::parse($lastActivity);
            }
            $daysInactive = (int) $lastActivity->startOfDay()->diffInDays(now()->startOfDay());

            // Grace period: first 14 days (no decay)
            if ($daysInactive < 14) {
                // Check if warning should be sent (day 12)
                if ($daysInactive === 12 && $this->shouldSendDecayWarning($pivot)) {
                    $user = User::find($pivot->user_id);
                    $this->sendDecayWarning($user, $group, $daysInactive);

                    // Update warning timestamp
                    DB::table('group_user')
                        ->where('id', $pivot->id)
                        ->update(['decay_warning_sent_at' => now()]);

                    $results['warnings_sent']++;
                }
                continue;
            }

            // Apply decay if past day 14
            if ($daysInactive >= 14) {
                // Check if decay already applied today
                if ($pivot->last_decay_applied_at) {
                    $hoursSinceDecay = now()->diffInHours($pivot->last_decay_applied_at);
                    if ($hoursSinceDecay < 24) {
                        continue; // Already decayed today
                    }
                }

                $user = User::find($pivot->user_id);
                $currentPoints = $pivot->points;

                if ($currentPoints <= 0) {
                    continue; // No points to decay
                }

                $decayAmount = $this->calculateDecayAmount($currentPoints);

                try {
                    DB::transaction(function () use ($user, $group, $decayAmount, $pivot, $currentPoints, $daysInactive) {
                        $balanceAfter = max(0, $currentPoints - $decayAmount);

                        // Update points
                        DB::table('group_user')
                            ->where('id', $pivot->id)
                            ->update([
                                'points' => $balanceAfter,
                                'last_decay_applied_at' => now(),
                            ]);

                        // Create transaction record
                        Transaction::create([
                            'user_id' => $user->id,
                            'group_id' => $group->id,
                            'type' => 'point_decay',
                            'amount' => -$decayAmount,
                            'balance_before' => $currentPoints,
                            'balance_after' => $balanceAfter,
                            'description' => "Point decay: {$decayAmount} pts (inactive {$daysInactive} days)",
                        ]);

                        // Send notification
                        $this->sendDecayApplied($user, $group, $decayAmount, $balanceAfter);
                    });

                    $results['decay_applied']++;
                } catch (\Exception $e) {
                    \Log::error("Failed to apply decay for user {$user->id} in group {$group->id}: " . $e->getMessage());
                }
            }
        }

        return $results;
    }

    /**
     * Send decay warning notification
     * TODO: Integrate with MessagingService when available
     */
    private function sendDecayWarning(User $user, Group $group, int $daysInactive): void
    {
        \Log::info("Decay warning for user {$user->id} in group {$group->id}: inactive {$daysInactive} days");
        // TODO: Send actual notification when messaging system is available
    }

    /**
     * Send decay applied notification
     * TODO: Integrate with MessagingService when available
     */
    private function sendDecayApplied(User $user, Group $group, int $amount, int $newBalance): void
    {
        \Log::info("Decay applied for user {$user->id} in group {$group->id}: -{$amount} pts, new balance: {$newBalance}");
        // TODO: Send actual notification when messaging system is available
    }
}
