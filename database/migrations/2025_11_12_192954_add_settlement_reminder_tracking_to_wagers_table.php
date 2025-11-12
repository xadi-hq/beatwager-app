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
            $table->timestamp('last_settlement_reminder_sent_at')->nullable()->after('settled_at');
            $table->unsignedInteger('settlement_reminder_count')->default(0)->after('last_settlement_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wagers', function (Blueprint $table) {
            $table->dropColumn(['last_settlement_reminder_sent_at', 'settlement_reminder_count']);
        });
    }
};
