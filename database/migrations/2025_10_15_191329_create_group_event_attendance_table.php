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
        Schema::create('group_event_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('group_events')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            $table->boolean('attended');
            $table->foreignUuid('reported_by_user_id')->constrained('users');
            $table->timestamp('reported_at');
            $table->boolean('bonus_awarded')->default(false);

            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
            $table->index(['event_id', 'attended']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_event_attendance');
    }
};
