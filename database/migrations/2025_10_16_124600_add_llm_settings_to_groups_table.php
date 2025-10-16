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
            // LLM Configuration (llm_api_key and bot_tone already added in earlier migration)
            $table->string('llm_provider')->default('anthropic')->after('bot_tone'); // anthropic, openai, etc.
            $table->enum('group_type', ['friends', 'family', 'colleagues', 'other'])->default('friends')->after('llm_provider');
            
            // Additional settings as JSON for flexibility
            $table->json('settings')->nullable()->after('group_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn([
                'llm_provider',
                'group_type',
                'settings'
            ]);
        });
    }
};