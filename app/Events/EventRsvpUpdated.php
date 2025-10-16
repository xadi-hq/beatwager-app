<?php

namespace App\Events;

use App\Models\GroupEvent;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventRsvpUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly GroupEvent $event,
        public readonly User $user,
        public readonly string $response,  // 'going', 'maybe', 'not_going'
        public readonly ?string $previousResponse = null  // Previous RSVP (null if first time)
    ) {}
}
