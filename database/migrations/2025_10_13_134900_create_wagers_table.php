<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wagers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('creator_id')->references('id')->on('users')->cascadeOnDelete();

            // Wager details
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('resolution_criteria')->nullable();

            // Wager type - supports multiple answer formats
            $table->enum('type', [
                'binary',           // Yes/No
                'multiple_choice',  // Pick from options (includes ternary 1/x/2)
                'numeric',          // Number guess
                'date'              // Date prediction
            ]);

            // For multiple_choice: array of options
            // e.g., ["1", "x", "2"] or ["Labour", "Conservative", "Green"]
            $table->json('options')->nullable();

            // For numeric: optional constraints
            $table->integer('numeric_min')->nullable();
            $table->integer('numeric_max')->nullable();
            $table->enum('numeric_winner_type', ['exact', 'closest'])->default('closest')->nullable();

            // For date: optional constraints
            $table->date('date_min')->nullable();
            $table->date('date_max')->nullable();
            $table->enum('date_winner_type', ['exact', 'closest'])->default('closest')->nullable();

            // Stakes
            $table->integer('stake_amount');

            // Timing
            $table->timestamp('deadline');
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('settled_at')->nullable();

            // Status and outcome
            $table->enum('status', [
                'open',
                'locked',
                'settled',
                'disputed',
                'cancelled'
            ])->default('open');

            // Outcome - flexible storage for any answer type
            $table->text('outcome_value')->nullable(); // "yes", "1", "42", "2025-06-15"
            $table->text('settlement_note')->nullable();

            // Statistics
            $table->integer('total_points_wagered')->default(0);
            $table->integer('participants_count')->default(0);

            $table->timestamps();

            $table->index(['group_id', 'status']);
            $table->index(['group_id', 'deadline']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wagers');
    }
};
