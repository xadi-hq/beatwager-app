<?php

declare(strict_types=1);

namespace App\Callbacks;

use App\Messaging\MessengerAdapterInterface;

/**
 * Abstract base class for callback handlers
 *
 * Provides common functionality and messenger adapter injection
 */
abstract class AbstractCallbackHandler implements CallbackHandlerInterface
{
    public function __construct(
        protected readonly MessengerAdapterInterface $messenger
    ) {}
}
