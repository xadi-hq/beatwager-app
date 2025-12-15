<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Badge;
use App\Models\Group;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a user earns a badge.
 */
class BadgeAwarded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly UserBadge $userBadge,
        public readonly User $user,
        public readonly Badge $badge,
        public readonly ?Group $group,
    ) {}
}
