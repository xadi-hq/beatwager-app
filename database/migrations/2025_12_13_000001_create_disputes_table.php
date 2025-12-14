<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();

            // Polymorphic relation to disputable item (Wager, Challenge, GroupEvent)
            $table->string('disputable_type');
            $table->uuid('disputable_id');

            // Parties involved
            $table->foreignUuid('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('accused_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_self_report')->default(false);

            // Status and resolution
            $table->string('status', 20)->default('pending'); // pending, resolved
            $table->string('resolution', 30)->nullable(); // original_correct, fraud_confirmed, premature_settlement

            // Outcome tracking
            $table->text('original_outcome');
            $table->text('corrected_outcome')->nullable();

            // Voting requirements
            $table->unsignedTinyInteger('votes_required');

            // Timing
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['disputable_type', 'disputable_id']);
            $table->index(['group_id', 'status']);
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
