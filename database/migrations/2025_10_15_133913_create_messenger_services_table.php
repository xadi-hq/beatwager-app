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
        Schema::create('messenger_services', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // Platform type (telegram, discord, slack)
            $table->string('platform', 20)->index();

            // Platform-specific user identifier (e.g., telegram_id, discord_id)
            $table->string('platform_user_id')->index();

            // User metadata from platform
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('photo_url')->nullable();

            // Additional platform-specific data (JSON for flexibility)
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Ensure one user can only have one account per platform
            $table->unique(['user_id', 'platform']);

            // Ensure platform user IDs are unique per platform
            $table->unique(['platform', 'platform_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messenger_services');
    }
};
