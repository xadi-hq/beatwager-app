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
        Schema::create('group_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('created_by_user_id')->constrained('users');

            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('event_date');
            $table->string('location')->nullable();
            $table->integer('attendance_bonus')->default(100);
            $table->timestamp('rsvp_deadline')->nullable();
            $table->integer('auto_prompt_hours_after')->default(2);

            $table->enum('status', ['upcoming', 'completed', 'expired', 'cancelled'])->default('upcoming');

            $table->timestamps();

            $table->index(['group_id', 'status']);
            $table->index('event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_events');
    }
};
