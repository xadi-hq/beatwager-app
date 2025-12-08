<?php

namespace App\Events;

use App\Models\ChallengeParticipant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EliminationChallengeTappedOut
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ChallengeParticipant $participant
    ) {}
}
