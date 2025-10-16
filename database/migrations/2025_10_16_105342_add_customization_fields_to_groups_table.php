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
            // Custom point currency name (e.g., "kudos", "eth", "points")
            $table->string('points_currency_name')->default('points')->after('name');

            // Bot notification preferences (JSON field)
            $table->json('notification_preferences')->nullable()->after('points_currency_name');

            // AI-powered bot personality (encrypted API key for OpenRouter/OpenAI-compatible APIs)
            $table->text('llm_api_key')->nullable()->after('notification_preferences');

            // Bot tone/personality instructions
            $table->text('bot_tone')->nullable()->after('llm_api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn([
                'points_currency_name',
                'notification_preferences',
                'llm_api_key',
                'bot_tone',
            ]);
        });
    }
};
