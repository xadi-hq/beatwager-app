<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Telegram group integration
            $table->bigInteger('telegram_chat_id')->unique();
            $table->string('telegram_chat_title')->nullable();
            $table->string('telegram_chat_type')->nullable(); // group, supergroup, channel
            
            // Point economy settings
            $table->integer('starting_balance')->default(1000);
            $table->boolean('point_decay_enabled')->default(false);
            $table->integer('point_decay_rate')->default(5); // percentage
            $table->integer('point_decay_grace_days')->default(14);
            
            // Group settings
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index('telegram_chat_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
