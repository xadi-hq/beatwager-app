<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wager>
 */
class WagerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bettingCloses = now()->addDays(fake()->numberBetween(1, 30));

        return [
            'group_id' => Group::factory(),
            'creator_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'type' => 'binary',
            'stake_amount' => fake()->numberBetween(10, 500),
            'betting_closes_at' => $bettingCloses,
            'expected_settlement_at' => $bettingCloses->copy()->addDays(fake()->numberBetween(1, 60)),
            'status' => 'open',
        ];
    }

    public function binary(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'binary',
        ]);
    }

    public function multipleChoice(array $options = ['1', 'x', '2']): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'multiple_choice',
            'options' => $options,
        ]);
    }

    public function numeric(int $min = 0, int $max = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'numeric',
            'numeric_min' => $min,
            'numeric_max' => $max,
            'numeric_winner_type' => 'closest',
        ]);
    }

    public function date(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'date',
            'date_min' => now()->addDays(1)->toDateString(),
            'date_max' => now()->addDays(365)->toDateString(),
            'date_winner_type' => 'closest',
        ]);
    }

    public function openEnded(): static
    {
        return $this->state(fn (array $attributes) => [
            'expected_settlement_at' => null,
        ]);
    }

    public function pastBettingDeadline(): static
    {
        return $this->state(fn (array $attributes) => [
            'betting_closes_at' => now()->subDays(fake()->numberBetween(1, 10)),
        ]);
    }

    public function closingSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'betting_closes_at' => now()->addHours(fake()->numberBetween(1, 48)),
        ]);
    }
}
