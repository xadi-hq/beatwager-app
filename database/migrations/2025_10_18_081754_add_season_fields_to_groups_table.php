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
            // Current active season reference
            $table->foreignUuid('current_season_id')->nullable()->after('settings')
                ->constrained('group_seasons')->nullOnDelete();

            // Optional: Custom season end date (if null, season runs indefinitely until manually ended)
            $table->timestamp('season_ends_at')->nullable()->after('current_season_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['current_season_id']);
            $table->dropColumn(['current_season_id', 'season_ends_at']);
        });
    }
};
