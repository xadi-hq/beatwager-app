<?php

namespace Database\Factories;

use App\Enums\ValidationStatus;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'challenge_id' => Challenge::factory(),
            'user_id' => User::factory(),
            'validation_status' => ValidationStatus::PENDING->value,
            'accepted_at' => now(),
            'completed_at' => null,
            'validated_by_creator_at' => null,
            'auto_validated_at' => null,
            'prize_transaction_id' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
        ]);
    }

    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_status' => ValidationStatus::VALIDATED->value,
            'validated_by_creator_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'validation_status' => ValidationStatus::REJECTED->value,
            'validated_by_creator_at' => now(),
        ]);
    }
}
