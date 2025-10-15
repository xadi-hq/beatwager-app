<?php

declare(strict_types=1);

namespace App\DTOs;

class Button
{
    public function __construct(
        public string $label,
        public ButtonAction $action,
        public string $value,
        public ButtonStyle $style = ButtonStyle::Default,
    ) {}
}

enum ButtonAction: string
{
    case Callback = 'callback';
    case Url = 'url';
}

enum ButtonStyle: string
{
    case Default = 'default';
    case Primary = 'primary';
    case Danger = 'danger';
}