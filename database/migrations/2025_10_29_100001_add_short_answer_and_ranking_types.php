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
        // Drop existing CHECK constraint
        DB::statement("ALTER TABLE wagers DROP CONSTRAINT IF EXISTS wagers_type_check");

        // Add new CHECK constraint with additional values
        DB::statement("ALTER TABLE wagers ADD CONSTRAINT wagers_type_check CHECK (type IN ('binary', 'multiple_choice', 'numeric', 'date', 'short_answer', 'top_n_ranking'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop constraint with new values
        DB::statement("ALTER TABLE wagers DROP CONSTRAINT IF EXISTS wagers_type_check");

        // Restore original constraint
        DB::statement("ALTER TABLE wagers ADD CONSTRAINT wagers_type_check CHECK (type IN ('binary', 'multiple_choice', 'numeric', 'date'))");
    }
};
