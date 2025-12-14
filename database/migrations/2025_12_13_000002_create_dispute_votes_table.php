<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispute_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dispute_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('voter_id')->constrained('users')->cascadeOnDelete();

            // Vote outcome: original_correct, different_outcome, not_yet_determinable
            $table->string('vote_outcome', 30);

            // If vote_outcome is 'different_outcome', store the selected outcome
            $table->text('selected_outcome')->nullable();

            $table->timestamp('created_at');

            // Each user can only vote once per dispute
            $table->unique(['dispute_id', 'voter_id']);

            // Index for counting votes
            $table->index(['dispute_id', 'vote_outcome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_votes');
    }
};
