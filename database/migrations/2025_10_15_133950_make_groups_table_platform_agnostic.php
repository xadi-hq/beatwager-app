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
            // Platform column already exists from 2025_10_14_145356_add_platform_to_groups_table.php

            // Rename telegram-specific columns to platform-agnostic names
            $table->renameColumn('telegram_chat_id', 'platform_chat_id');
            $table->renameColumn('telegram_chat_title', 'platform_chat_title');
            $table->renameColumn('telegram_chat_type', 'platform_chat_type');
        });

        // Update index after rename
        Schema::table('groups', function (Blueprint $table) {
            $table->dropUnique(['telegram_chat_id']);
            $table->dropIndex(['telegram_chat_id']);
            $table->unique(['platform', 'platform_chat_id']);
            $table->index('platform_chat_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // Revert index changes
            $table->dropUnique(['platform', 'platform_chat_id']);
            $table->dropIndex(['platform_chat_id']);
        });

        Schema::table('groups', function (Blueprint $table) {
            // Rename back to telegram-specific columns
            $table->renameColumn('platform_chat_id', 'telegram_chat_id');
            $table->renameColumn('platform_chat_title', 'telegram_chat_title');
            $table->renameColumn('platform_chat_type', 'telegram_chat_type');

            // Don't remove platform column - it's managed by 2025_10_14_145356_add_platform_to_groups_table.php
        });

        // Restore original indexes
        Schema::table('groups', function (Blueprint $table) {
            $table->unique('telegram_chat_id');
            $table->index('telegram_chat_id');
        });
    }
};
