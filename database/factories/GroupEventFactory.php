<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupEvent>
 */
class GroupEventFactory extends Factory
{
    protected $model = GroupEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'created_by_user_id' => User::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'event_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'location' => $this->faker->optional()->address(),
            'attendance_bonus' => $this->faker->numberBetween(50, 500),
            'rsvp_deadline' => $this->faker->optional()->dateTimeBetween('now', '+2 weeks'),
            'auto_prompt_hours_after' => $this->faker->numberBetween(1, 6),
            'status' => 'upcoming',
        ];
    }

    /**
     * Indicate that the event is completed.
     */
    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'event_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            ];
        });
    }

    /**
     * Indicate that the event is expired.
     */
    public function expired(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
                'event_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            ];
        });
    }

    /**
     * Indicate that the event is cancelled.
     */
    public function cancelled(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }
}