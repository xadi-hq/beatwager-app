<?php

declare(strict_types=1);

namespace App\Enums;

enum ValidationStatus: string
{
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case REJECTED = 'rejected';
}
