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
        Schema::table('users', function (Blueprint $table) {
            // Drop telegram-specific columns
            $table->dropUnique(['telegram_id']);
            $table->dropIndex(['telegram_id']);
            $table->dropColumn([
                'telegram_id',
                'telegram_username',
                'telegram_first_name',
                'telegram_last_name',
                'telegram_photo_url',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore telegram columns for rollback
            $table->bigInteger('telegram_id')->unique();
            $table->string('telegram_username')->nullable();
            $table->string('telegram_first_name')->nullable();
            $table->string('telegram_last_name')->nullable();
            $table->string('telegram_photo_url')->nullable();
            $table->index('telegram_id');
        });
    }
};
