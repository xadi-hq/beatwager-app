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
        Schema::table('messenger_services', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('metadata');
            
            // Add index for faster lookups
            $table->index(['user_id', 'is_primary']);
        });
        
        // Set the first messenger service for each user as primary
        DB::statement('
            UPDATE messenger_services
            SET is_primary = true
            WHERE id IN (
                SELECT MIN(id)
                FROM messenger_services
                GROUP BY user_id
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messenger_services', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_primary']);
            $table->dropColumn('is_primary');
        });
    }
};