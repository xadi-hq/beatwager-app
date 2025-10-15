<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wager_entries', function (Blueprint $table) {
            $table->boolean('is_winner')->default(false)->after('points_wagered');
        });
    }

    public function down(): void
    {
        Schema::table('wager_entries', function (Blueprint $table) {
            $table->dropColumn('is_winner');
        });
    }
};
