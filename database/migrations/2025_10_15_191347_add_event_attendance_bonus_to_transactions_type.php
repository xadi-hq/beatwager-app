<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'event_attendance_bonus' to the transaction type enum column
        // PostgreSQL requires recreating the constraint to add enum values
        DB::statement("
            ALTER TABLE transactions
            DROP CONSTRAINT IF EXISTS transactions_type_check
        ");

        DB::statement("
            ALTER TABLE transactions
            ADD CONSTRAINT transactions_type_check
            CHECK (type IN (
                'wager_placed',
                'wager_won',
                'wager_lost',
                'wager_refunded',
                'point_decay',
                'admin_adjustment',
                'initial_balance',
                'event_attendance_bonus'
            ))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate without event_attendance_bonus
        DB::statement("
            ALTER TABLE transactions
            DROP CONSTRAINT IF EXISTS transactions_type_check
        ");

        DB::statement("
            ALTER TABLE transactions
            ADD CONSTRAINT transactions_type_check
            CHECK (type IN (
                'wager_placed',
                'wager_won',
                'wager_lost',
                'wager_refunded',
                'point_decay',
                'admin_adjustment',
                'initial_balance'
            ))
        ");
    }
};
