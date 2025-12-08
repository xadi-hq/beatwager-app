<?php

namespace App\Events;

use App\Models\Challenge;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched for milestones: half_eliminated, final_two
 */
class EliminationChallengeMilestone
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Challenge $challenge,
        public readonly string $milestone // 'half_eliminated' | 'final_two'
    ) {}
}
