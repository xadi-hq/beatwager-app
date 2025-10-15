<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Group',
            'platform' => 'telegram',
            'platform_chat_id' => (string) fake()->unique()->numberBetween(-9999999, -1000000), // Telegram groups have negative IDs
            'platform_chat_title' => fake()->words(2, true),
            'platform_chat_type' => 'group',
            'starting_balance' => 1000,
            'is_active' => true,
        ];
    }
}
