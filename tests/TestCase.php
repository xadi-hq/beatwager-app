<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\MocksAuditServices;

abstract class TestCase extends BaseTestCase
{
    use MocksAuditServices;

    protected $defaultMiddleware = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF middleware for all tests
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Mock audit services by default (prevents null reference errors)
        $this->mockAuditServices();
    }
}
