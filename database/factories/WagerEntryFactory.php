<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Models\WagerEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WagerEntry>
 */
class WagerEntryFactory extends Factory
{
    protected $model = WagerEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wager_id' => Wager::factory(),
            'user_id' => User::factory(),
            'group_id' => Group::factory(),
            'answer_value' => $this->faker->randomElement(['yes', 'no']),
            'points_wagered' => $this->faker->numberBetween(50, 200),
            'result' => null,
            'is_winner' => false,
            'points_won' => 0,
            'points_lost' => 0,
            'numeric_distance' => null,
            'date_distance_days' => null,
        ];
    }

    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'won',
            'is_winner' => true,
            'points_won' => $attributes['points_wagered'] * 2,
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'lost',
            'is_winner' => false,
            'points_lost' => $attributes['points_wagered'],
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'refunded',
        ]);
    }
}