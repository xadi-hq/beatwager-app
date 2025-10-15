<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Who performed the action (null = system)
            $table->foreignUuid('actor_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('actor_type', 50)->default('User'); // User, System, Bot
            
            // What action was performed
            $table->string('action', 100); // wager.created, wager.settled, transaction.created
            
            // What was affected (polymorphic)
            $table->string('auditable_type', 100)->nullable(); // Wager, User, Transaction
            $table->uuid('auditable_id')->nullable();
            
            // Context and metadata
            $table->json('metadata')->nullable(); // Flexible data: old/new values, platform, etc.
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamp('created_at');
            
            // Indexes for common queries
            $table->index('actor_id');
            $table->index('action');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
