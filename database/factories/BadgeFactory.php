<?php

namespace Database\Factories;

use App\Enums\BadgeCategory;
use App\Enums\BadgeCriteriaType;
use App\Enums\BadgeTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Badge>
 */
class BadgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => 'badge_' . fake()->unique()->lexify('????_????'),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(BadgeCategory::cases()),
            'tier' => BadgeTier::Standard,
            'is_shame' => false,
            'criteria_type' => BadgeCriteriaType::First,
            'criteria_event' => 'wager_won',
            'criteria_threshold' => null,
            'criteria_config' => null,
            'image_slug' => 'badge_' . fake()->lexify('????_????'),
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    public function wagers(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => BadgeCategory::Wagers,
        ]);
    }

    public function events(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => BadgeCategory::Events,
        ]);
    }

    public function challenges(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => BadgeCategory::Challenges,
        ]);
    }

    public function disputes(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => BadgeCategory::Disputes,
        ]);
    }

    public function first(): static
    {
        return $this->state(fn (array $attributes) => [
            'criteria_type' => BadgeCriteriaType::First,
            'criteria_threshold' => null,
            'tier' => BadgeTier::Standard,
        ]);
    }

    public function countType(int $threshold = 5): static
    {
        $tier = match (true) {
            $threshold >= 20 => BadgeTier::Gold,
            $threshold >= 10 => BadgeTier::Silver,
            $threshold >= 5 => BadgeTier::Bronze,
            default => BadgeTier::Standard,
        };

        return $this->state(fn (array $attributes) => [
            'criteria_type' => BadgeCriteriaType::Count,
            'criteria_threshold' => $threshold,
            'tier' => $tier,
        ]);
    }

    public function streak(int $threshold = 5): static
    {
        return $this->state(fn (array $attributes) => [
            'criteria_type' => BadgeCriteriaType::Streak,
            'criteria_threshold' => $threshold,
        ]);
    }

    public function comparative(): static
    {
        return $this->state(fn (array $attributes) => [
            'criteria_type' => BadgeCriteriaType::Comparative,
            'criteria_threshold' => null,
            'criteria_config' => ['scope' => 'group', 'metric' => 'most'],
            'tier' => BadgeTier::Gold,
        ]);
    }

    public function shame(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shame' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function bronze(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => BadgeTier::Bronze,
        ]);
    }

    public function silver(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => BadgeTier::Silver,
        ]);
    }

    public function gold(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => BadgeTier::Gold,
        ]);
    }

    public function platinum(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => BadgeTier::Platinum,
        ]);
    }
}
