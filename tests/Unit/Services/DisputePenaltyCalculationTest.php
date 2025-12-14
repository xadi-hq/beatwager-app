<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\TransactionType;
use App\Models\Group;
use App\Models\MessengerService;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisputePenaltyCalculationTest extends TestCase
{
    use RefreshDatabase;

    private PointService $pointService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pointService = app(PointService::class);
    }

    // ========================================
    // deductPercentage Method Tests
    // ========================================

    /** @test */
    public function deducts_10_percent_for_false_report_penalty()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $transaction = $this->pointService->deductPercentage(
            $user,
            $group,
            10,
            TransactionType::DisputePenaltyFalseReport
        );

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals(-100, $transaction->amount);
        $this->assertEquals(1000, $transaction->balance_before);
        $this->assertEquals(900, $transaction->balance_after);

        // Verify balance updated
        $balance = $user->groups()->where('group_id', $group->id)->first()->pivot->points;
        $this->assertEquals(900, $balance);
    }

    /** @test */
    public function deducts_5_percent_for_honest_mistake_penalty()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $transaction = $this->pointService->deductPercentage(
            $user,
            $group,
            5,
            TransactionType::DisputePenaltyHonestMistake
        );

        $this->assertEquals(-50, $transaction->amount);
        $this->assertEquals(950, $transaction->balance_after);
    }

    /** @test */
    public function deducts_25_percent_for_first_fraud_offense()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $transaction = $this->pointService->deductPercentage(
            $user,
            $group,
            25,
            TransactionType::DisputePenaltyFraud
        );

        $this->assertEquals(-250, $transaction->amount);
        $this->assertEquals(750, $transaction->balance_after);
    }

    /** @test */
    public function deducts_50_percent_for_repeat_fraud_offense()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $transaction = $this->pointService->deductPercentage(
            $user,
            $group,
            50,
            TransactionType::DisputePenaltyFraud
        );

        $this->assertEquals(-500, $transaction->amount);
        $this->assertEquals(500, $transaction->balance_after);
    }

    /** @test */
    public function rounds_up_percentage_calculation()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        // 333 * 10% = 33.3, should round up to 34
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 333,
            'role' => 'participant',
        ]);

        $transaction = $this->pointService->deductPercentage(
            $user,
            $group,
            10,
            TransactionType::DisputePenaltyFalseReport
        );

        $this->assertEquals(-34, $transaction->amount);
        $this->assertEquals(299, $transaction->balance_after);
    }

    /** @test */
    public function deducts_minimum_1_point_when_balance_greater_than_zero()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        // 5 * 10% = 0.5, should become 1
        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 5,
            'role' => 'participant',
        ]);

        $transaction = $this->pointService->deductPercentage(
            $user,
            $group,
            10,
            TransactionType::DisputePenaltyFalseReport
        );

        $this->assertEquals(-1, $transaction->amount);
        $this->assertEquals(4, $transaction->balance_after);
    }

    /** @test */
    public function creates_zero_transaction_when_balance_is_zero()
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $group->users()->attach($user->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 0,
            'role' => 'participant',
        ]);

        $transaction = $this->pointService->deductPercentage(
            $user,
            $group,
            10,
            TransactionType::DisputePenaltyFalseReport
        );

        $this->assertEquals(0, $transaction->amount);
        $this->assertEquals(0, $transaction->balance_before);
        $this->assertEquals(0, $transaction->balance_after);
    }

    // ========================================
    // MessengerService Fraud Tracking Tests
    // ========================================

    /** @test */
    public function messenger_service_returns_25_percent_for_first_offense()
    {
        $user = User::factory()->create();
        $messengerService = MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'telegram',
            'platform_user_id' => '123456',
            'fraud_offense_count' => 0,
        ]);

        $this->assertEquals(25, $messengerService->getFraudPenaltyPercentage());
        $this->assertFalse($messengerService->isRepeatOffender());
    }

    /** @test */
    public function messenger_service_returns_50_percent_for_repeat_offense()
    {
        $user = User::factory()->create();
        $messengerService = MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'telegram',
            'platform_user_id' => '123456',
            'fraud_offense_count' => 1,
        ]);

        $this->assertEquals(50, $messengerService->getFraudPenaltyPercentage());
        $this->assertTrue($messengerService->isRepeatOffender());
    }

    /** @test */
    public function messenger_service_returns_50_percent_for_multiple_offenses()
    {
        $user = User::factory()->create();
        $messengerService = MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'telegram',
            'platform_user_id' => '123456',
            'fraud_offense_count' => 5,
        ]);

        $this->assertEquals(50, $messengerService->getFraudPenaltyPercentage());
        $this->assertTrue($messengerService->isRepeatOffender());
    }

    /** @test */
    public function increment_fraud_count_updates_offense_count_and_timestamp()
    {
        $user = User::factory()->create();
        $messengerService = MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'telegram',
            'platform_user_id' => '123456',
            'fraud_offense_count' => 0,
            'last_fraud_at' => null,
        ]);

        $this->assertEquals(0, $messengerService->fraud_offense_count);
        $this->assertNull($messengerService->last_fraud_at);

        $messengerService->incrementFraudCount();
        $messengerService->refresh();

        $this->assertEquals(1, $messengerService->fraud_offense_count);
        $this->assertNotNull($messengerService->last_fraud_at);
        $this->assertTrue(now()->diffInSeconds($messengerService->last_fraud_at) < 5);

        // Penalty percentage should now be 50%
        $this->assertEquals(50, $messengerService->getFraudPenaltyPercentage());
    }

    /** @test */
    public function fraud_tracking_is_per_platform_identity()
    {
        $user = User::factory()->create();

        // Same user, different platforms - separate fraud tracking
        $telegramService = MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'telegram',
            'platform_user_id' => '123456',
            'fraud_offense_count' => 2,
        ]);

        $discordService = MessengerService::create([
            'user_id' => $user->id,
            'platform' => 'discord',
            'platform_user_id' => '789012',
            'fraud_offense_count' => 0,
        ]);

        // Telegram is repeat offender (50%)
        $this->assertEquals(50, $telegramService->getFraudPenaltyPercentage());
        $this->assertTrue($telegramService->isRepeatOffender());

        // Discord is first offense (25%)
        $this->assertEquals(25, $discordService->getFraudPenaltyPercentage());
        $this->assertFalse($discordService->isRepeatOffender());
    }

    // ========================================
    // Penalty Matrix Integration Tests
    // ========================================

    /** @test */
    public function penalty_matrix_false_dispute_10_percent()
    {
        $group = Group::factory()->create();
        $reporter = User::factory()->create();

        $group->users()->attach($reporter->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        // False dispute = 10% penalty on reporter
        $transaction = $this->pointService->deductPercentage(
            $reporter,
            $group,
            10,
            TransactionType::DisputePenaltyFalseReport
        );

        $this->assertEquals(-100, $transaction->amount);
        $this->assertEquals(TransactionType::DisputePenaltyFalseReport->value, is_object($transaction->type) ? $transaction->type->value : $transaction->type);
    }

    /** @test */
    public function penalty_matrix_honest_mistake_5_percent()
    {
        $group = Group::factory()->create();
        $settler = User::factory()->create();

        $group->users()->attach($settler->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        // Honest mistake (self-report) = 5% penalty on settler
        $transaction = $this->pointService->deductPercentage(
            $settler,
            $group,
            5,
            TransactionType::DisputePenaltyHonestMistake
        );

        $this->assertEquals(-50, $transaction->amount);
        $this->assertEquals(TransactionType::DisputePenaltyHonestMistake->value, is_object($transaction->type) ? $transaction->type->value : $transaction->type);
    }

    /** @test */
    public function penalty_matrix_premature_settlement()
    {
        $group = Group::factory()->create();
        $settler = User::factory()->create();

        $group->users()->attach($settler->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        // Premature settlement = 25% (first) or 50% (repeat) + outcome cleared
        $transaction = $this->pointService->deductPercentage(
            $settler,
            $group,
            25,
            TransactionType::DisputePenaltyPremature
        );

        $this->assertEquals(-250, $transaction->amount);
        $this->assertEquals(TransactionType::DisputePenaltyPremature->value, is_object($transaction->type) ? $transaction->type->value : $transaction->type);
    }
}
