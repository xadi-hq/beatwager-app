<?php

declare(strict_types=1);

namespace App\Enums;

enum ChallengeType: string
{
    case USER_CHALLENGE = 'user_challenge';
    case SUPER_CHALLENGE = 'super_challenge';
    case ELIMINATION_CHALLENGE = 'elimination_challenge';
}
