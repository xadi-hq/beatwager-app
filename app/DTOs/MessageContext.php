<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Group;

class MessageContext
{
    public function __construct(
        public readonly string $key,              // e.g., 'wager.announced'
        public readonly string $intent,           // human-readable purpose
        public readonly array $requiredFields,    // fields that must be present
        public readonly array $data,              // raw data payload (wager, event, user, etc.)
        public readonly ?Group $group = null,     // optional group for tone
    ) {}
}
