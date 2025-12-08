<?php

namespace App\Events;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class EliminationChallengeResolved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Challenge $challenge
     * @param Collection<int, ChallengeParticipant> $survivors
     */
    public function __construct(
        public readonly Challenge $challenge,
        public readonly Collection $survivors
    ) {}
}
