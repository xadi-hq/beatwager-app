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
            // last_wager_joined_at already exists, only add the new columns
            $table->timestamp('last_decay_applied_at')->nullable()->after('last_wager_joined_at');
            $table->timestamp('decay_warning_sent_at')->nullable()->after('last_decay_applied_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->dropColumn(['last_decay_applied_at', 'decay_warning_sent_at']);
        });
    }
};
