<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Group context
            $table->foreignUuid('group_id')->references('id')->on('groups')->cascadeOnDelete();
            
            // Event classification
            $table->string('event_type', 100)->index(); // wager.won, badge.earned, grudge.updated, etc.
            
            // Human-readable summary for LLM context
            $table->text('summary'); // "Sarah won 'Marathon Bet' against John for 50 points"
            
            // Participants (users involved)
            $table->json('participants'); // [{user_id, username, role}, ...]
            
            // Impact/results
            $table->json('impact')->nullable(); // {points: 50, badge: 'streak_breaker', ...}
            
            // Additional metadata
            $table->json('metadata')->nullable(); // Flexible: {wager_id, streak_count, ...}
            
            $table->timestamp('created_at');
            
            // Indexes for common queries
            $table->index(['group_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
