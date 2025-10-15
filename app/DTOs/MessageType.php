<?php

declare(strict_types=1);

namespace App\DTOs;

enum MessageType: string
{
    case Announcement = 'announcement';
    case Confirmation = 'confirmation';
    case Reminder = 'reminder';
    case Error = 'error';
    case Result = 'result';
    case Info = 'info';
    case Warning = 'warning';
}
