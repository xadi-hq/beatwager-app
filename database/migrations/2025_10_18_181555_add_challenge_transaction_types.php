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
        // Add challenge-related transaction types to the enum constraint
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
                'event_attendance_bonus',
                'challenge_hold',
                'challenge_completed',
                'challenge_failed',
                'challenge_cancelled'
            ))
        ");
        
        // Add reference to challenges table
        DB::statement("
            ALTER TABLE transactions
            ADD COLUMN challenge_id UUID NULL
        ");
        
        DB::statement("
            ALTER TABLE transactions
            ADD CONSTRAINT fk_transactions_challenge_id
            FOREIGN KEY (challenge_id) REFERENCES challenges(id)
            ON DELETE CASCADE
        ");
        
        // Add index for challenge_id
        DB::statement("
            CREATE INDEX idx_transactions_challenge_id ON transactions(challenge_id)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove challenge_id column and constraint
        DB::statement("
            DROP INDEX IF EXISTS idx_transactions_challenge_id
        ");
        
        DB::statement("
            ALTER TABLE transactions
            DROP CONSTRAINT IF EXISTS fk_transactions_challenge_id
        ");
        
        DB::statement("
            ALTER TABLE transactions
            DROP COLUMN IF EXISTS challenge_id
        ");
        
        // Recreate constraint without challenge types
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
};
