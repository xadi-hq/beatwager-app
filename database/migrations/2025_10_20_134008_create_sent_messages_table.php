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
        Schema::create('sent_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Context
            $table->foreignUuid('group_id')->references('id')->on('groups')->cascadeOnDelete();

            // Message classification
            $table->string('message_type', 100)->index();
            // Examples: 'wager.announcement', 'engagement.prompt', 'birthday.reminder',
            //           'weekly.recap', 'decay.warning', 'event.announcement'

            // Optional: Link to specific context (wager, event, challenge, user)
            $table->uuid('context_id')->nullable()->index();
            $table->string('context_type')->nullable(); // 'wager', 'event', 'challenge', 'user', 'scheduled_message'

            // Message content summary (for LLM context)
            $table->text('summary')->nullable();
            // Example: "Engagement prompt sent for 'Marathon Bet' wager"

            // Additional metadata
            $table->json('metadata')->nullable();
            // Example: {'wager_id': '...', 'participant_count': 0, 'reason': 'low_engagement'}

            $table->timestamp('sent_at');

            // Indexes for anti-spam queries
            $table->index(['group_id', 'message_type', 'sent_at']);
            $table->index(['context_type', 'context_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_messages');
    }
};
