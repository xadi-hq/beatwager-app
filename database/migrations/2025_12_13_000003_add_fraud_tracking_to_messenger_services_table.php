<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messenger_services', function (Blueprint $table) {
            // Track fraud offenses globally per platform identity
            $table->unsignedInteger('fraud_offense_count')->default(0)->after('is_primary');
            $table->timestamp('last_fraud_at')->nullable()->after('fraud_offense_count');
        });
    }

    public function down(): void
    {
        Schema::table('messenger_services', function (Blueprint $table) {
            $table->dropColumn(['fraud_offense_count', 'last_fraud_at']);
        });
    }
};
