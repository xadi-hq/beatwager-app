<?php

namespace App\Events;

use App\Models\ChallengeParticipant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuperChallengeCompletionValidated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly ChallengeParticipant $participant,
        public readonly bool $approved
    ) {}
}
