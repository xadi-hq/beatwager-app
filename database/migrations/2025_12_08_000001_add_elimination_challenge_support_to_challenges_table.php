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
            // Elimination challenge specific fields
            $table->string('elimination_trigger')
                ->nullable()
                ->comment('What eliminates participants (e.g., "Hearing Last Christmas")')
                ->after('evidence_guidance');

            $table->string('elimination_mode', 50)
                ->nullable()
                ->comment('last_man_standing or deadline')
                ->after('elimination_trigger');

            $table->integer('point_pot')
                ->nullable()
                ->comment('Total pot to distribute to survivors')
                ->after('elimination_mode');

            $table->integer('buy_in_amount')
                ->nullable()
                ->comment('Per-person buy-in commitment')
                ->after('point_pot');

            $table->timestamp('tap_in_deadline')
                ->nullable()
                ->comment('When tap-in registration closes')
                ->after('buy_in_amount');

            $table->integer('min_participants')
                ->default(3)
                ->comment('Minimum participants to activate elimination challenge')
                ->after('tap_in_deadline');

            // Index for filtering elimination challenges
            $table->index(['type', 'elimination_mode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropIndex(['type', 'elimination_mode']);

            $table->dropColumn([
                'elimination_trigger',
                'elimination_mode',
                'point_pot',
                'buy_in_amount',
                'tap_in_deadline',
                'min_participants',
            ]);
        });
    }
};
