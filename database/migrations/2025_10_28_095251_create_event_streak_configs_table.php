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
        Schema::create('group_event_streak_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->json('multiplier_tiers')->nullable();
            $table->timestamps();

            $table->unique('group_id'); // One config per group
        });

        // Create default configs for existing groups
        DB::table('groups')->get()->each(function ($group) {
            DB::table('group_event_streak_configs')->insert([
                'id' => (string) Str::uuid(),
                'group_id' => $group->id,
                'enabled' => false,
                'multiplier_tiers' => json_encode([
                    ['min' => 1, 'max' => 3, 'multiplier' => 1.0],
                    ['min' => 4, 'max' => 6, 'multiplier' => 1.2],
                    ['min' => 7, 'max' => 9, 'multiplier' => 1.4],
                    ['min' => 10, 'max' => 19, 'multiplier' => 1.5],
                    ['min' => 20, 'max' => null, 'multiplier' => 2.0],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_event_streak_configs');
    }
};
