<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupSeason>
 */
class GroupSeasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'season_number' => fake()->numberBetween(1, 10),
            'started_at' => now()->subDays(30),
            'ended_at' => null,
            'is_active' => true,
            'final_leaderboard' => null,
            'stats' => null,
            'highlights' => null,
            'prize_structure' => null,
        ];
    }

    /**
     * Indicate that the season has ended.
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => now(),
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the season has a prize structure.
     */
    public function withPrizes(): static
    {
        return $this->state(fn (array $attributes) => [
            'prize_structure' => [
                ['position' => 'winner', 'description' => '$50 gift card'],
                ['position' => 'runner_up', 'description' => '$25 gift card'],
            ],
        ]);
    }
}
