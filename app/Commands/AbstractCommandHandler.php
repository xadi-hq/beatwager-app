<?php

declare(strict_types=1);

namespace App\Commands;

use App\Messaging\MessengerAdapterInterface;

/**
 * Base class for command handlers providing common functionality
 */
abstract class AbstractCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        protected readonly MessengerAdapterInterface $messenger
    ) {}

    /**
     * Default: no aliases
     *
     * @return array<string>
     */
    public function getAliases(): array
    {
        return [];
    }
}
