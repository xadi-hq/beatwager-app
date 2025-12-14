<?php

namespace Database\Factories;

use App\Enums\DisputeStatus;
use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dispute>
 */
class DisputeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'disputable_type' => Wager::class,
            'disputable_id' => Wager::factory(),
            'reporter_id' => User::factory(),
            'accused_id' => User::factory(),
            'is_self_report' => false,
            'status' => DisputeStatus::Pending,
            'original_outcome' => 'yes',
            'votes_required' => 2,
            'expires_at' => now()->addHours(48),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DisputeStatus::Pending,
            'resolution' => null,
            'resolved_at' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DisputeStatus::Resolved,
            'resolution' => \App\Enums\DisputeResolution::OriginalCorrect,
            'resolved_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DisputeStatus::Pending,
            'expires_at' => now()->subHour(),
        ]);
    }

    public function selfReport(): static
    {
        return $this->state(function (array $attributes) {
            $userId = $attributes['reporter_id'] ?? User::factory()->create()->id;
            return [
                'reporter_id' => $userId,
                'accused_id' => $userId,
                'is_self_report' => true,
            ];
        });
    }
}
