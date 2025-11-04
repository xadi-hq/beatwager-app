<?php

namespace Database\Factories;

use App\Enums\NudgeResponse;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuperChallengeNudgeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'nudged_user_id' => User::factory(),
            'response' => NudgeResponse::PENDING->value,
            'nudged_at' => now(),
            'responded_at' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'response' => NudgeResponse::ACCEPTED->value,
            'responded_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'response' => NudgeResponse::DECLINED->value,
            'responded_at' => now(),
        ]);
    }
}
