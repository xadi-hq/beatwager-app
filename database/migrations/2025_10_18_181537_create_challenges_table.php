<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('acceptor_id')->nullable()->constrained('users')->cascadeOnDelete();
            
            // Challenge details
            $table->text('description');
            $table->integer('amount');
            
            // Timing
            $table->timestamp('acceptance_deadline')->nullable();
            $table->timestamp('completion_deadline')->nullable(); // Nullable for last_man_standing elimination challenges
            
            // Status tracking
            $table->enum('status', ['open', 'accepted', 'completed', 'failed', 'cancelled'])->default('open');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Verification and failure details
            $table->foreignUuid('verified_by_id')->nullable()->constrained('users');
            $table->foreignUuid('cancelled_by_id')->nullable()->constrained('users');
            $table->text('failure_reason')->nullable();
            
            // Submission details
            $table->text('submission_note')->nullable();
            $table->json('submission_media')->nullable();
            
            // Points system integration
            $table->foreignUuid('hold_transaction_id')->nullable()->constrained('transactions');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['group_id', 'status']);
            $table->index(['acceptor_id', 'status']);
            $table->index(['creator_id', 'status']);
            $table->index('acceptance_deadline');
            $table->index('completion_deadline');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
