<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_badges', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Core relationships
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('badge_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('group_id')->nullable()->constrained()->cascadeOnDelete(); // null = global, set = group-specific

            // Timing
            $table->timestamp('awarded_at');
            $table->timestamp('revoked_at')->nullable();       // Set when badge is revoked
            $table->string('revocation_reason')->nullable();   // Why it was revoked

            // Context
            $table->json('metadata')->nullable(); // Context: triggering wager_id, streak details, etc.
            $table->timestamp('notified_at')->nullable(); // When notification was sent

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'badge_id']);
            $table->index(['user_id', 'group_id']);
            $table->index(['badge_id', 'group_id']);
            $table->index('awarded_at');
            $table->index('revoked_at');

            // Unique constraint: user can only have one active (non-revoked) instance of a badge per context
            // Note: This is enforced at application level since MySQL doesn't support partial unique indexes
            // The application will check: UNIQUE(user_id, badge_id, group_id) where revoked_at IS NULL
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
