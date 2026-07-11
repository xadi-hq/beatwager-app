<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\GroupSeason;
use Carbon\Carbon;
use Tests\TestCase;

class GroupSeasonModelTest extends TestCase
{
    /** @test */
    public function get_duration_in_days_returns_null_when_not_ended()
    {
        $season = GroupSeason::factory()->make([
            'started_at' => now()->subDays(5),
            'ended_at' => null,
        ]);

        $this->assertNull($season->getDurationInDays());
    }

    /** @test */
    public function get_duration_in_days_returns_int_for_whole_day_span()
    {
        $season = GroupSeason::factory()->make([
            'started_at' => Carbon::parse('2026-01-01 00:00:00'),
            'ended_at' => Carbon::parse('2026-01-11 00:00:00'),
        ]);

        $duration = $season->getDurationInDays();

        $this->assertIsInt($duration);
        $this->assertSame(10, $duration);
    }

    /**
     * Carbon 3's diffInDays() returns a float. Under strict_types this would
     * raise a TypeError against the ?int return type if not cast — which was
     * the cause of the 500 when ending a season.
     *
     * @test
     */
    public function get_duration_in_days_does_not_throw_for_sub_day_span()
    {
        $season = GroupSeason::factory()->make([
            'started_at' => Carbon::parse('2026-01-01 00:00:00'),
            'ended_at' => Carbon::parse('2026-01-01 12:00:00'),
        ]);

        $duration = $season->getDurationInDays();

        $this->assertIsInt($duration);
        $this->assertSame(0, $duration);
    }
}
