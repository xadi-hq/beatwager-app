<?php

namespace Database\Factories;

use App\Models\GroupEvent;
use App\Models\GroupEventRsvp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupEventRsvp>
 */
class GroupEventRsvpFactory extends Factory
{
    protected $model = GroupEventRsvp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => GroupEvent::factory(),
            'user_id' => User::factory(),
            'response' => $this->faker->randomElement(['going', 'maybe', 'not_going']),
        ];
    }

    /**
     * Indicate that the user is going.
     */
    public function going(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'response' => 'going',
            ];
        });
    }

    /**
     * Indicate that the user might go.
     */
    public function maybe(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'response' => 'maybe',
            ];
        });
    }

    /**
     * Indicate that the user is not going.
     */
    public function notGoing(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'response' => 'not_going',
            ];
        });
    }
}