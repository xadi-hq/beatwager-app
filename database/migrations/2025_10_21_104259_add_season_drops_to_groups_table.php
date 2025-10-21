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
            // Enable/disable automatic season milestone drops
            $table->boolean('surprise_drops_enabled')->default(false)->after('has_seasons');

            // Track which milestones have been triggered for current season
            // JSON array: ["50", "75", "90"] to prevent duplicate drops
            $table->json('season_milestones_triggered')->nullable()->after('surprise_drops_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['surprise_drops_enabled', 'season_milestones_triggered']);
        });
    }
};
