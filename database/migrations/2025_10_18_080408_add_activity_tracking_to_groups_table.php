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
        Schema::table('groups', function (Blueprint $table) {
            // Activity tracking fields
            $table->timestamp('last_activity_at')->nullable()->after('is_active');
            $table->integer('inactivity_threshold_days')->default(14)->after('last_activity_at');

            // Index for efficient queries of inactive groups
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['last_activity_at']);
            $table->dropColumn(['last_activity_at', 'inactivity_threshold_days']);
        });
    }
};
