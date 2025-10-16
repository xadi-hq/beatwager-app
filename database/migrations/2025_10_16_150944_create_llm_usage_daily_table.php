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
        Schema::create('llm_usage_daily', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->onDelete('cascade');
            $table->date('date')->index();

            // Aggregate metrics
            $table->integer('total_calls')->default(0);
            $table->integer('cached_calls')->default(0);
            $table->integer('fallback_calls')->default(0);
            $table->decimal('estimated_cost_usd', 10, 4)->default(0);

            // Breakdown details
            $table->json('providers_breakdown')->nullable(); // {anthropic: 5, openai: 3, requesty: 10}
            $table->json('message_types')->nullable(); // {wager.announced: 10, wager.settled: 5}

            $table->timestamps();

            // One row per group per day
            $table->unique(['group_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_usage_daily');
    }
};
