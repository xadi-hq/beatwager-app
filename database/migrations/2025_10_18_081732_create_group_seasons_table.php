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
        Schema::create('group_seasons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->onDelete('cascade');
            $table->integer('season_number'); // 1, 2, 3, etc.
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_active')->default(true);

            // Final standings stored as JSON
            $table->json('final_leaderboard')->nullable(); // [{user_id, name, points, rank}]

            // Season statistics and highlights
            $table->json('stats')->nullable(); // Calculated stats when season ends

            // Year in Review data for LLM-powered recap
            $table->json('highlights')->nullable(); // {biggest_win, longest_streak, most_wagers, etc.}

            $table->timestamps();

            // Indexes
            $table->index(['group_id', 'is_active']);
            $table->index(['group_id', 'season_number']);
            $table->unique(['group_id', 'season_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_seasons');
    }
};
