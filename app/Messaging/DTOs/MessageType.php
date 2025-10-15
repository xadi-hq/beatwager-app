<?php

declare(strict_types=1);

namespace App\Messaging\DTOs;

/**
 * Platform-agnostic message types
 */
enum MessageType: string
{
    case COMMAND = 'command';          // Bot command (/start, /help)
    case TEXT = 'text';                // Regular text message
    case CALLBACK = 'callback';        // Button callback/interaction
    case INTERACTION = 'interaction';  // Slash command interaction (Slack/Discord)
}
