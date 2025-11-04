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
        Schema::table('challenges', function (Blueprint $table) {
            // SuperChallenge type differentiation (use PHP enum, not DB enum)
            $table->string('type', 50)
                ->default('user_challenge')
                ->after('id');

            // Prize structure (per-person, not total pool)
            $table->integer('prize_per_person')
                ->nullable()
                ->comment('Amount each completer receives (not total pool)')
                ->after('amount');

            $table->integer('max_participants')
                ->nullable()
                ->comment('Max number of users who can accept this SuperChallenge')
                ->after('prize_per_person');

            // Creator guidance for participants
            $table->text('evidence_guidance')
                ->nullable()
                ->comment('Optional creator guidance on how to prove completion')
                ->after('max_participants');

            // Make amount nullable for SuperChallenges (they use prize_per_person instead)
            $table->integer('amount')
                ->nullable()
                ->change();

            // Make creator_id nullable for system challenges
            $table->foreignUuid('creator_id')
                ->nullable()
                ->change();

            // Index for filtering by type
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'prize_per_person',
                'max_participants',
                'evidence_guidance',
            ]);

            // Restore creator_id as non-nullable
            $table->foreignUuid('creator_id')
                ->nullable(false)
                ->change();

            $table->dropIndex(['type']);
        });
    }
};
