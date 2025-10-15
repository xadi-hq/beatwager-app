<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge>
 */
class ChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $acceptanceDeadline = now()->addDays(fake()->numberBetween(1, 7));
        $completionDeadline = $acceptanceDeadline->copy()->addDays(fake()->numberBetween(1, 30));

        return [
            'group_id' => Group::factory(),
            'creator_id' => User::factory(),
            'description' => fake()->sentence(),
            'amount' => fake()->numberBetween(10, 500),
            'acceptance_deadline' => $acceptanceDeadline,
            'completion_deadline' => $completionDeadline,
            'status' => 'open',
        ];
    }

    public function accepted(?User $acceptor = null): static
    {
        return $this->state(fn (array $attributes) => [
            'acceptor_id' => $acceptor?->id ?? User::factory(),
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
            'verified_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'failed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function pastAcceptanceDeadline(): static
    {
        return $this->state(fn (array $attributes) => [
            'acceptance_deadline' => now()->subDays(fake()->numberBetween(1, 10)),
        ]);
    }

    public function pastCompletionDeadline(): static
    {
        return $this->state(fn (array $attributes) => [
            'completion_deadline' => now()->subDays(fake()->numberBetween(1, 10)),
        ]);
    }
}
