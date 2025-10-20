<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Removes the positive amount constraint to allow negative amounts.
     * Positive amounts = Type 1 (creator pays, acceptor receives)
     * Negative amounts = Type 2 (acceptor pays, creator receives)
     */
    public function up(): void
    {
        // Drop the positive amount constraint
        DB::statement('ALTER TABLE challenges DROP CONSTRAINT IF EXISTS challenges_amount_positive');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only restore constraint if no negative amounts exist
        $hasNegativeAmounts = DB::table('challenges')
            ->where('amount', '<', 0)
            ->exists();

        if (!$hasNegativeAmounts) {
            DB::statement('ALTER TABLE challenges ADD CONSTRAINT challenges_amount_positive CHECK (amount > 0)');
        } else {
            throw new \Exception('Cannot restore constraint: negative amounts exist in challenges table');
        }
    }
};
