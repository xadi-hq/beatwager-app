<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Wager;
use Carbon\Carbon;
use Tests\TestCase;

class WagerModelLogicTest extends TestCase
{
    /** @test */
    public function is_binary_returns_correct_type()
    {
        $binaryWager = Wager::factory()->make(['type' => 'binary']);
        $numericWager = Wager::factory()->make(['type' => 'numeric']);

        $this->assertTrue($binaryWager->isBinary());
        $this->assertFalse($numericWager->isBinary());
    }

    /** @test */
    public function is_numeric_returns_correct_type()
    {
        $numericWager = Wager::factory()->make(['type' => 'numeric']);
        $dateWager = Wager::factory()->make(['type' => 'date']);

        $this->assertTrue($numericWager->isNumeric());
        $this->assertFalse($dateWager->isNumeric());
    }

    /** @test */
    public function is_date_returns_correct_type()
    {
        $dateWager = Wager::factory()->make(['type' => 'date']);
        $binaryWager = Wager::factory()->make(['type' => 'binary']);

        $this->assertTrue($dateWager->isDate());
        $this->assertFalse($binaryWager->isDate());
    }

    /** @test */
    public function get_display_options_returns_correct_options_for_binary()
    {
        $binaryWager = Wager::factory()->make(['type' => 'binary']);

        $options = $binaryWager->getDisplayOptions();

        $this->assertEquals(['Yes', 'No'], $options);
    }

    /** @test */
    public function get_display_options_returns_correct_options_for_multiple_choice()
    {
        $options = ['Team A', 'Team B', 'Team C'];
        $multipleChoiceWager = Wager::factory()->make([
            'type' => 'multiple_choice',
            'options' => $options
        ]);

        $displayOptions = $multipleChoiceWager->getDisplayOptions();

        $this->assertEquals($options, $displayOptions);
    }

    /** @test */
    public function is_betting_open_when_status_open_and_before_deadline()
    {
        $wager = Wager::factory()->make([
            'status' => 'open',
            'betting_closes_at' => Carbon::now()->addHours(2)
        ]);

        $this->assertTrue($wager->isBettingOpen());
    }

    /** @test */
    public function is_not_betting_open_when_status_not_open()
    {
        $wager = Wager::factory()->make([
            'status' => 'locked',
            'betting_closes_at' => Carbon::now()->addHours(2)
        ]);

        $this->assertFalse($wager->isBettingOpen());
    }

    /** @test */
    public function is_not_betting_open_when_past_deadline()
    {
        $wager = Wager::factory()->make([
            'status' => 'open',
            'betting_closes_at' => Carbon::now()->subHours(1)
        ]);

        $this->assertFalse($wager->isBettingOpen());
    }

    /** @test */
    public function is_past_betting_deadline_with_past_deadline()
    {
        $wager = Wager::factory()->make([
            'betting_closes_at' => Carbon::now()->subHours(1)
        ]);

        $this->assertTrue($wager->isPastBettingDeadline());
    }

    /** @test */
    public function is_not_past_betting_deadline_with_future_deadline()
    {
        $wager = Wager::factory()->make([
            'betting_closes_at' => Carbon::now()->addHours(1)
        ]);

        $this->assertFalse($wager->isPastBettingDeadline());
    }

    /** @test */
    public function has_expected_settlement_when_date_set()
    {
        $wager = Wager::factory()->make([
            'expected_settlement_at' => Carbon::now()->addDays(1)
        ]);

        $this->assertTrue($wager->hasExpectedSettlement());
    }

    /** @test */
    public function does_not_have_expected_settlement_when_null()
    {
        $wager = Wager::factory()->make([
            'expected_settlement_at' => null
        ]);

        $this->assertFalse($wager->hasExpectedSettlement());
    }

    /** @test */
    public function get_settlement_trigger_date_returns_expected_settlement_when_set()
    {
        $expectedDate = Carbon::now()->addDays(3);
        $bettingCloses = Carbon::now()->addDays(1);
        
        $wager = Wager::factory()->make([
            'expected_settlement_at' => $expectedDate,
            'betting_closes_at' => $bettingCloses
        ]);

        $triggerDate = $wager->getSettlementTriggerDate();

        $this->assertEquals($expectedDate->toDateTimeString(), $triggerDate->toDateTimeString());
    }

    /** @test */
    public function get_settlement_trigger_date_returns_betting_closes_when_no_expected_settlement()
    {
        $bettingCloses = Carbon::now()->addDays(1);
        
        $wager = Wager::factory()->make([
            'expected_settlement_at' => null,
            'betting_closes_at' => $bettingCloses
        ]);

        $triggerDate = $wager->getSettlementTriggerDate();

        $this->assertEquals($bettingCloses->toDateTimeString(), $triggerDate->toDateTimeString());
    }

    /** @test */
    public function is_past_settlement_trigger_when_expected_settlement_is_past()
    {
        $wager = Wager::factory()->make([
            'expected_settlement_at' => Carbon::now()->subHours(2),
            'betting_closes_at' => Carbon::now()->subDays(1)
        ]);

        $this->assertTrue($wager->isPastSettlementTrigger());
    }

    /** @test */
    public function is_not_past_settlement_trigger_when_expected_settlement_is_future()
    {
        $wager = Wager::factory()->make([
            'expected_settlement_at' => Carbon::now()->addHours(2),
            'betting_closes_at' => Carbon::now()->subDays(1)
        ]);

        $this->assertFalse($wager->isPastSettlementTrigger());
    }
}
