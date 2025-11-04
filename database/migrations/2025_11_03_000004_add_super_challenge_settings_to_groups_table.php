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
        Schema::table('groups', function (Blueprint $table) {
            // SuperChallenge frequency configuration (use PHP enum, not DB enum)
            $table->string('superchallenge_frequency', 50)
                ->default('off')
                ->after('is_active');

            // Track last SuperChallenge creation
            $table->timestamp('last_superchallenge_at')
                ->nullable()
                ->after('superchallenge_frequency');

            // Index for eligibility queries
            $table->index('last_superchallenge_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn([
                'superchallenge_frequency',
                'last_superchallenge_at',
            ]);

            $table->dropIndex(['last_superchallenge_at']);
        });
    }
};
