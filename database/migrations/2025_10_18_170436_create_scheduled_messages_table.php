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
        Schema::create('scheduled_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->onDelete('cascade');

            // Message details
            $table->enum('message_type', ['holiday', 'birthday', 'custom'])->default('custom');
            $table->string('title');
            $table->date('scheduled_date');
            $table->text('message_template')->nullable(); // Optional custom template
            $table->text('llm_instructions')->nullable(); // Optional custom LLM guidance

            // Recurrence settings
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_type', ['yearly', 'monthly', 'weekly', 'daily'])->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['group_id', 'scheduled_date']);
            $table->index(['group_id', 'is_active']);
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_messages');
    }
};
