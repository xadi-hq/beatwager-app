<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\PointService;
use Carbon\Carbon;
use Tests\TestCase;

class PointServiceDecayTest extends TestCase
{
    private PointService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PointService::class);
    }

    /** @test */
    public function calculates_decay_amount_with_minimum_bound()
    {
        // 5% of 100 = 5, but min is 50
        $this->assertEquals(50, $this->service->calculateDecayAmount(100));
        
        // 5% of 900 = 45, but min is 50
        $this->assertEquals(50, $this->service->calculateDecayAmount(900));
        
        // 5% of 999 = 49.95, ceil to 50, min is 50
        $this->assertEquals(50, $this->service->calculateDecayAmount(999));
    }

    /** @test */
    public function calculates_decay_amount_with_maximum_bound()
    {
        // 5% of 5000 = 250, but max is 100
        $this->assertEquals(100, $this->service->calculateDecayAmount(5000));
        
        // 5% of 10000 = 500, but max is 100
        $this->assertEquals(100, $this->service->calculateDecayAmount(10000));
    }

    /** @test */
    public function calculates_decay_amount_in_middle_range()
    {
        // 5% of 2000 = 100 (exactly at max bound)
        $this->assertEquals(100, $this->service->calculateDecayAmount(2000));
        
        // 5% of 1500 = 75 (within bounds)
        $this->assertEquals(75, $this->service->calculateDecayAmount(1500));
        
        // 5% of 1200 = 60 (within bounds)
        $this->assertEquals(60, $this->service->calculateDecayAmount(1200));
    }

    /** @test */
    public function calculates_decay_for_edge_case_zero_points()
    {
        // 5% of 0 = 0, but min is 50
        $this->assertEquals(50, $this->service->calculateDecayAmount(0));
    }

    /** @test */
    public function should_send_decay_warning_on_day_12()
    {
        $pivot = (object) [
            'last_wager_joined_at' => Carbon::now()->subDays(12)->toDateTimeString(),
            'decay_warning_sent_at' => null,
        ];
        
        $this->assertTrue($this->service->shouldSendDecayWarning($pivot));
    }

    /** @test */
    public function should_not_send_decay_warning_before_day_12()
    {
        $pivot = (object) [
            'last_wager_joined_at' => Carbon::now()->subDays(11)->toDateTimeString(),
            'decay_warning_sent_at' => null,
        ];
        
        $this->assertFalse($this->service->shouldSendDecayWarning($pivot));
    }

    /** @test */
    public function should_not_send_decay_warning_after_day_12()
    {
        $pivot = (object) [
            'last_wager_joined_at' => Carbon::now()->subDays(13)->toDateTimeString(),
            'decay_warning_sent_at' => null,
        ];
        
        $this->assertFalse($this->service->shouldSendDecayWarning($pivot));
    }

    /** @test */
    public function should_not_send_decay_warning_if_already_sent_recently()
    {
        $pivot = (object) [
            'last_wager_joined_at' => Carbon::now()->subDays(12)->toDateTimeString(),
            'decay_warning_sent_at' => Carbon::now()->subHours(12)->toDateTimeString(),
        ];
        
        $this->assertFalse($this->service->shouldSendDecayWarning($pivot));
    }


    /** @test */
    public function handles_null_last_wager_joined_at_for_warning()
    {
        $pivot = (object) [
            'last_wager_joined_at' => null,
            'created_at' => Carbon::now()->subDays(12)->toDateTimeString(),
            'decay_warning_sent_at' => null,
        ];
        
        $this->assertTrue($this->service->shouldSendDecayWarning($pivot));
    }
}
