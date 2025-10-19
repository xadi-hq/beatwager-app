<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Services\AuditEventService;
use App\Services\AuditService;
use Mockery;

trait MocksAuditServices
{
    protected function mockAuditServices(): void
    {
        // Mock AuditService for all tests
        $auditServiceMock = Mockery::mock('overload:' . AuditService::class);
        $auditServiceMock->shouldReceive('log')->andReturnNull()->byDefault();

        // Mock AuditEventService for all tests
        $auditEventServiceMock = Mockery::mock('overload:' . AuditEventService::class);
        $auditEventServiceMock->shouldReceive('wagerWon')->andReturnNull()->byDefault();
        $auditEventServiceMock->shouldReceive('create')->andReturnNull()->byDefault();
    }
}
