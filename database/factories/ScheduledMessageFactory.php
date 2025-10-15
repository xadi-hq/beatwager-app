<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\ScheduledMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledMessageFactory extends Factory
{
    protected $model = ScheduledMessage::class;

    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'message_type' => 'custom',
            'title' => fake()->sentence(),
            'scheduled_date' => now()->addDays(7),
            'message_template' => fake()->paragraph(),
            'llm_instructions' => null,
            'is_recurring' => false,
            'recurrence_type' => null,
            'is_active' => true,
            'last_sent_at' => null,
            'is_drop_event' => false,
            'drop_amount' => null,
        ];
    }

    public function drop(int $amount = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'is_drop_event' => true,
            'drop_amount' => $amount,
        ]);
    }

    public function birthday(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'birthday',
            'is_drop_event' => false, // Birthday messages can't be drops
            'drop_amount' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function recurring(string $type = 'weekly'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_type' => $type,
        ]);
    }
}
