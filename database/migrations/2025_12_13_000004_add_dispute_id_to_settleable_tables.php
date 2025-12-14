<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add dispute_id to wagers
        Schema::table('wagers', function (Blueprint $table) {
            $table->foreignUuid('dispute_id')
                ->nullable()
                ->after('settler_id')
                ->constrained('disputes')
                ->nullOnDelete();
        });

        // Add dispute_id to challenges
        Schema::table('challenges', function (Blueprint $table) {
            $table->foreignUuid('dispute_id')
                ->nullable()
                ->after('verified_by_id')
                ->constrained('disputes')
                ->nullOnDelete();
        });

        // Add dispute_id to group_events
        Schema::table('group_events', function (Blueprint $table) {
            $table->foreignUuid('dispute_id')
                ->nullable()
                ->after('creator_id')
                ->constrained('disputes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wagers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dispute_id');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dispute_id');
        });

        Schema::table('group_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dispute_id');
        });
    }
};
