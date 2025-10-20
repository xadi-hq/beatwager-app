<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wager_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wager_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();

            // User's answer - flexible based on wager type
            // For binary/multiple_choice: "yes", "1", "Labour"
            // For numeric: "42"
            // For date: "2025-06-15"
            $table->text('answer_value');

            // For numeric: track distance from correct answer (calculated at settlement)
            $table->integer('numeric_distance')->nullable();

            // For date: track distance in days from correct answer
            $table->integer('date_distance_days')->nullable();

            // Points
            $table->integer('points_wagered');

            // Outcome (calculated at settlement)
            $table->boolean('is_winner')->default(false);
            $table->enum('result', ['won', 'lost', 'refunded'])->nullable();
            $table->integer('points_won')->default(0);
            $table->integer('points_lost')->default(0);

            $table->timestamps();

            $table->unique(['wager_id', 'user_id']);
            $table->index(['user_id', 'group_id']);
            $table->index('wager_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wager_entries');
    }
};
