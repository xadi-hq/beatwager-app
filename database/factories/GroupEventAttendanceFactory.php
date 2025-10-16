<?php

namespace Database\Factories;

use App\Models\GroupEvent;
use App\Models\GroupEventAttendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupEventAttendance>
 */
class GroupEventAttendanceFactory extends Factory
{
    protected $model = GroupEventAttendance::class;

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
            'attended' => $this->faker->boolean(70), // 70% chance of attendance
            'reported_by_user_id' => User::factory(),
            'reported_at' => now(),
            'bonus_awarded' => false,
        ];
    }

    /**
     * Indicate that the user attended.
     */
    public function attended(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'attended' => true,
            ];
        });
    }

    /**
     * Indicate that the user did not attend.
     */
    public function notAttended(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'attended' => false,
            ];
        });
    }

    /**
     * Indicate that the bonus was awarded.
     */
    public function bonusAwarded(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'bonus_awarded' => true,
                'attended' => true, // Can only award bonus if attended
            ];
        });
    }
}