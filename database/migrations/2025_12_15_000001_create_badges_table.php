<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Badge identification
            $table->string('slug', 50)->unique(); // e.g., 'wager_won_5', 'first_event_attended'
            $table->string('name', 100);          // Display name, e.g., 'Winning Streak'
            $table->text('description');          // e.g., 'Won 5 wagers'

            // Classification
            $table->string('category', 20);       // BadgeCategory enum: wagers, events, challenges, disputes, special
            $table->string('tier', 20);           // BadgeTier enum: standard, bronze, silver, gold, platinum
            $table->boolean('is_shame')->default(false); // For negative achievements (fraudster, no-shows)

            // Criteria for earning
            $table->string('criteria_type', 20);  // BadgeCriteriaType enum: first, count, streak, comparative
            $table->string('criteria_event', 50); // Event that triggers check, e.g., 'wager_won', 'event_attended'
            $table->unsignedInteger('criteria_threshold')->nullable(); // Count needed (null for 'first' type)
            $table->json('criteria_config')->nullable(); // Additional criteria params (e.g., streak window, comparison scope)

            // Display
            $table->string('image_slug', 100);    // Maps to image file, e.g., 'wager-won-5'
            $table->unsignedInteger('sort_order')->default(0); // For display ordering within category

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('category');
            $table->index('tier');
            $table->index('criteria_event');
            $table->index('is_active');
            $table->index(['category', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
