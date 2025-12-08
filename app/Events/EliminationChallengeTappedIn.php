<?php

namespace App\Events;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EliminationChallengeTappedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Challenge $challenge,
        public readonly User $user
    ) {}
}
