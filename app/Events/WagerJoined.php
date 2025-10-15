<?php

namespace App\Events;

use App\Models\Wager;
use App\Models\WagerEntry;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WagerJoined
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Wager $wager,
        public readonly WagerEntry $entry,
        public readonly User $user
    ) {}
}
