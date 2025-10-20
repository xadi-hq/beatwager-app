<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('platform')->default('telegram');
            $table->text('description')->nullable();

            // Platform-agnostic group integration
            $table->bigInteger('platform_chat_id');
            $table->string('platform_chat_title')->nullable();
            $table->string('platform_chat_type')->nullable(); // group, supergroup, channel

            // Customization fields
            $table->string('points_currency_name')->default('points');
            $table->json('notification_preferences')->nullable();

            // LLM Configuration
            $table->text('llm_api_key')->nullable();
            $table->text('bot_tone')->nullable();
            $table->boolean('allow_nsfw')->default(false);
            $table->string('llm_provider')->default('anthropic'); // anthropic, openai, etc.
            $table->enum('group_type', ['friends', 'family', 'colleagues', 'other'])->default('friends');

            // Additional settings as JSON for flexibility
            $table->json('settings')->nullable();

            // Season management (foreign key added later in group_seasons migration)
            $table->uuid('current_season_id')->nullable();
            $table->timestamp('season_ends_at')->nullable();

            // Point economy settings
            $table->integer('starting_balance')->default(1000);
            $table->boolean('point_decay_enabled')->default(false);
            $table->integer('point_decay_rate')->default(5); // percentage
            $table->integer('point_decay_grace_days')->default(14);

            // Group settings
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_activity_at')->nullable();
            $table->integer('inactivity_threshold_days')->default(14);

            $table->timestamps();

            $table->unique(['platform', 'platform_chat_id']);
            $table->index('platform_chat_id');
            $table->index('is_active');
            $table->index('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
