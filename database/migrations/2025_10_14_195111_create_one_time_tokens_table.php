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
        Schema::create('one_time_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token', 64)->unique();

            // Optional user (null if user doesn't exist yet, e.g., wager creation)
            $table->foreignUuid('user_id')->nullable()->references('id')->on('users')->cascadeOnDelete();

            // Token type (wager_creation, wager_settlement, event_attendance, etc.)
            $table->string('type', 50);

            // Flexible context data (platform info, wager_id, event_id, etc.)
            $table->json('context');

            // Usage tracking
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();

            $table->timestamps();

            $table->index('token');
            $table->index('user_id');
            $table->index('type');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_time_tokens');
    }
};
