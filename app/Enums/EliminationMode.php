<?php

declare(strict_types=1);

namespace App\Enums;

enum EliminationMode: string
{
    case LAST_MAN_STANDING = 'last_man_standing';
    case DEADLINE = 'deadline';
}
