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
            // Track the last countdown milestone sent (hours remaining: 24, 12, 6, 1)
            // null = no countdown sent yet
            $table->unsignedSmallInteger('last_countdown_hours')->nullable()->after('point_pot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('last_countdown_hours');
        });
    }
};
