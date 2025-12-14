<?php

namespace Database\Factories;

use App\Enums\DisputeVoteOutcome;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DisputeVote>
 */
class DisputeVoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dispute_id' => Dispute::factory(),
            'voter_id' => User::factory(),
            'vote_outcome' => DisputeVoteOutcome::OriginalCorrect,
            'selected_outcome' => null,
        ];
    }

    public function originalCorrect(): static
    {
        return $this->state(fn (array $attributes) => [
            'vote_outcome' => DisputeVoteOutcome::OriginalCorrect,
            'selected_outcome' => null,
        ]);
    }

    public function differentOutcome(string $outcome): static
    {
        return $this->state(fn (array $attributes) => [
            'vote_outcome' => DisputeVoteOutcome::DifferentOutcome,
            'selected_outcome' => $outcome,
        ]);
    }

    public function notYetDeterminable(): static
    {
        return $this->state(fn (array $attributes) => [
            'vote_outcome' => DisputeVoteOutcome::NotYetDeterminable,
            'selected_outcome' => null,
        ]);
    }
}
