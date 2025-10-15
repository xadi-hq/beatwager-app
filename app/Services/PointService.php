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
            DB::table("user_group")
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
            DB::table("user_group")
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
}
