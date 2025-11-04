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
        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // Participation timeline
            $table->timestamp('accepted_at');
            $table->timestamp('completed_at')->nullable();

            // Validation tracking (use PHP enum, not DB enum)
            $table->string('validation_status', 50)
                ->default('pending');
            $table->timestamp('validated_by_creator_at')->nullable();
            $table->timestamp('auto_validated_at')->nullable()->comment('Set if auto-approved after 48h timeout');

            // Points transaction reference
            $table->foreignUuid('prize_transaction_id')->nullable()->constrained('transactions');

            $table->timestamps();

            // Unique constraint: one participation per user per challenge
            $table->unique(['challenge_id', 'user_id']);

            // Indexes for queries
            $table->index(['challenge_id', 'validation_status']);
            $table->index(['user_id', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_participants');
    }
};
