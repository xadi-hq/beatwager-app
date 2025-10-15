<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_group', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();
            
            // Point economy for this user in this group
            $table->integer('points')->default(1000);
            $table->integer('points_earned')->default(0);
            $table->integer('points_spent')->default(0);
            
            // Activity tracking
            $table->timestamp('last_wager_joined_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            
            // Role in group
            $table->enum('role', ['participant', 'admin'])->default('participant');
            
            $table->timestamps();
            
            $table->unique(['user_id', 'group_id']);
            $table->index('points');
            $table->index(['group_id', 'points']); // For leaderboards
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_group');
    }
};
