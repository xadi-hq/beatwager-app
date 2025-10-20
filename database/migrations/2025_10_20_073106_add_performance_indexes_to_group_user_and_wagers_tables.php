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
        // Add index on last_activity_at for activity tracking queries
        Schema::table('group_user', function (Blueprint $table) {
            $table->index('last_activity_at');
            $table->index(['group_id', 'last_activity_at']); // For group activity queries
        });

        // Add single-column index on betting_closes_at for deadline checks
        // Note: composite index (group_id, betting_closes_at) already exists
        Schema::table('wagers', function (Blueprint $table) {
            $table->index('betting_closes_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->dropIndex(['last_activity_at']);
            $table->dropIndex(['group_id', 'last_activity_at']);
        });

        Schema::table('wagers', function (Blueprint $table) {
            $table->dropIndex(['betting_closes_at']);
        });
    }
};
