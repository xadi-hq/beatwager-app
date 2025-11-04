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
        Schema::create('super_challenge_nudges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('nudged_user_id')->constrained('users')->cascadeOnDelete();

            // Response tracking (use PHP enum, not DB enum)
            $table->string('response', 50)
                ->default('pending');

            // Timeline
            $table->timestamp('nudged_at');
            $table->timestamp('responded_at')->nullable();

            // Link to created challenge (if accepted)
            $table->foreignUuid('created_challenge_id')->nullable()->constrained('challenges')->nullOnDelete();

            $table->timestamps();

            // Indexes for queries
            $table->index(['group_id', 'nudged_at']);
            $table->index(['nudged_user_id', 'response']);
            $table->index('response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_challenge_nudges');
    }
};
