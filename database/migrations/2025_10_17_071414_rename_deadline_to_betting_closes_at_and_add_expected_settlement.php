<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wagers', function (Blueprint $table) {
            // Rename deadline to betting_closes_at
            $table->renameColumn('deadline', 'betting_closes_at');

            // Add optional expected settlement date
            $table->timestamp('expected_settlement_at')->nullable()->after('betting_closes_at');
        });

        // Update the index
        Schema::table('wagers', function (Blueprint $table) {
            $table->dropIndex(['group_id', 'deadline']);
            $table->index(['group_id', 'betting_closes_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wagers', function (Blueprint $table) {
            // Remove expected_settlement_at
            $table->dropColumn('expected_settlement_at');

            // Rename back to deadline
            $table->renameColumn('betting_closes_at', 'deadline');
        });

        // Restore the original index
        Schema::table('wagers', function (Blueprint $table) {
            $table->dropIndex(['group_id', 'betting_closes_at']);
            $table->index(['group_id', 'deadline']);
        });
    }
};
