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
        Schema::table('group_event_attendance', function (Blueprint $table) {
            $table->integer('streak_at_time')->nullable()->after('bonus_awarded');
            $table->decimal('multiplier_applied', 3, 2)->nullable()->after('streak_at_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_event_attendance', function (Blueprint $table) {
            $table->dropColumn(['streak_at_time', 'multiplier_applied']);
        });
    }
};
