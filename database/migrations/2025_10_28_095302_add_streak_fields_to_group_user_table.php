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
        Schema::table('group_user', function (Blueprint $table) {
            $table->integer('event_attendance_streak')->default(0)->after('last_wager_joined_at');
            $table->timestamp('last_event_attended_at')->nullable()->after('event_attendance_streak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->dropColumn(['event_attendance_streak', 'last_event_attended_at']);
        });
    }
};
