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
        Schema::table('challenge_participants', function (Blueprint $table) {
            // Elimination-specific fields
            $table->timestamp('eliminated_at')
                ->nullable()
                ->comment('When participant tapped out / was eliminated')
                ->after('completed_at');

            $table->string('elimination_note')
                ->nullable()
                ->comment('Optional self-reported elimination context for LLM messaging')
                ->after('eliminated_at');

            // Buy-in transaction reference (for refunds on cancel)
            $table->foreignUuid('buy_in_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->after('prize_transaction_id');

            // Index for elimination challenge queries
            $table->index(['challenge_id', 'eliminated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_participants', function (Blueprint $table) {
            $table->dropIndex(['challenge_id', 'eliminated_at']);
            $table->dropForeign(['buy_in_transaction_id']);

            $table->dropColumn([
                'eliminated_at',
                'elimination_note',
                'buy_in_transaction_id',
            ]);
        });
    }
};
