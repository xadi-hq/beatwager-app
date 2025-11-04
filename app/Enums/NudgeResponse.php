<?php

declare(strict_types=1);

namespace App\Enums;

enum NudgeResponse: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case EXPIRED = 'expired';
}
