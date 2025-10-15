<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ApplyPointDecay;
use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Services\PointService;
use App\Services\WagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PointDecayTest extends TestCase
{
    use RefreshDatabase;

    private PointService $pointService;
    private WagerService $wagerService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock messenger services to avoid sending actual messages
        $mockMessenger = \Mockery::mock(\App\Services\Messengers\TelegramMessenger::class);
        $mockMessenger->shouldReceive('formatMessage')->andReturn('test message');
        $mockMessenger->shouldReceive('send')->andReturn(null);
        
        $mockFactory = \Mockery::mock(\App\Services\MessengerFactory::class);
        $mockFactory->shouldReceive('create')->andReturn($mockMessenger);
        
        $this->app->instance(\App\Services\MessengerFactory::class, $mockFactory);
        
        $this->pointService = app(PointService::class);
        $this->wagerService = app(WagerService::class);
    }

    public function test_calculate_decay_amount_with_min_bound(): void
    {
        // 5% of 1000 = 50, which is at the min bound
        $this->assertEquals(50, $this->pointService->calculateDecayAmount(1000));
    }

    public function test_calculate_decay_amount_with_max_bound(): void
    {
        // 5% of 5000 = 250, capped at max 100
        $this->assertEquals(100, $this->pointService->calculateDecayAmount(5000));
    }

    public function test_calculate_decay_amount_in_middle_range(): void
    {
        // 5% of 1500 = 75, within bounds
        $this->assertEquals(75, $this->pointService->calculateDecayAmount(1500));
    }

    public function test_last_wager_joined_at_updated_when_placing_wager(): void
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $this->pointService->initializeUserPoints($user, $group);

        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'status' => 'open',
            'stake_amount' => 100,
        ]);

        // Place wager
        $this->wagerService->placeWager($wager, $user, 'yes', 100);

        // Check that last_wager_joined_at was updated
        $pivot = DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->first();

        $this->assertNotNull($pivot->last_wager_joined_at);
        $this->assertTrue(now()->diffInSeconds($pivot->last_wager_joined_at) < 2);
    }

    public function test_no_decay_during_grace_period(): void
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $this->pointService->initializeUserPoints($user, $group);

        // Set last_wager_joined_at to 10 days ago (within 14-day grace period)
        DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->update(['last_wager_joined_at' => now()->subDays(10)->toDateTimeString()]);

        // Apply decay
        $results = $this->pointService->applyDecayForGroup($group);

        // No decay should be applied
        $this->assertEquals(0, $results['decay_applied']);

        // Balance should still be 1000
        $balance = $this->pointService->getBalance($user, $group);
        $this->assertEquals(1000, $balance);
    }

    public function test_decay_applied_after_14_days_inactivity(): void
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $this->pointService->initializeUserPoints($user, $group);

        // Set last_wager_joined_at to 15 days ago
        DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->update([
                'last_wager_joined_at' => now()->subDays(15)->toDateTimeString(),
                'created_at' => now()->subDays(20)->toDateTimeString(),
            ]);

        // Apply decay
        $results = $this->pointService->applyDecayForGroup($group);

        // Decay should be applied once
        $this->assertEquals(1, $results['decay_applied']);

        // Balance should be 1000 - 50 = 950 (min decay is 50)
        $balance = $this->pointService->getBalance($user, $group);
        $this->assertEquals(950, $balance);

        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'group_id' => $group->id,
            'type' => 'point_decay',
            'amount' => -50,
        ]);
    }

    public function test_decay_not_applied_twice_in_same_day(): void
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $this->pointService->initializeUserPoints($user, $group);

        // Set last_wager_joined_at to 15 days ago and last_decay_applied_at to 1 hour ago
        DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->update([
                'last_wager_joined_at' => now()->subDays(15)->toDateTimeString(),
                'last_decay_applied_at' => now()->subHour()->toDateTimeString(),
                'created_at' => now()->subDays(20)->toDateTimeString(),
            ]);

        // Apply decay
        $results = $this->pointService->applyDecayForGroup($group);

        // No decay should be applied (already done today)
        $this->assertEquals(0, $results['decay_applied']);

        // Balance should still be 1000
        $balance = $this->pointService->getBalance($user, $group);
        $this->assertEquals(1000, $balance);
    }

    public function test_decay_stops_when_user_joins_wager(): void
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $this->pointService->initializeUserPoints($user, $group);

        // Simulate 20 days of inactivity
        DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->update([
                'last_wager_joined_at' => now()->subDays(20)->toDateTimeString(),
                'created_at' => now()->subDays(25)->toDateTimeString(),
            ]);

        // Apply decay once
        $this->pointService->applyDecayForGroup($group);
        $balanceAfterDecay = $this->pointService->getBalance($user, $group);
        $this->assertEquals(950, $balanceAfterDecay); // 1000 - 50

        // User joins a wager
        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'status' => 'open',
            'stake_amount' => 100,
        ]);
        $this->wagerService->placeWager($wager, $user, 'yes', 100);

        // Reset last_decay_applied_at to allow decay check
        DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->update(['last_decay_applied_at' => now()->subDays(2)->toDateTimeString()]);

        // Apply decay again - should not apply because last_wager_joined_at is recent
        $results = $this->pointService->applyDecayForGroup($group);
        $this->assertEquals(0, $results['decay_applied']);

        // Balance should be 850 (950 - 100 stake, no additional decay)
        $balance = $this->pointService->getBalance($user, $group);
        $this->assertEquals(850, $balance);
    }

    public function test_decay_warning_sent_on_day_12(): void
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $this->pointService->initializeUserPoints($user, $group);

        // Mock messenger service
        $user->messengerServices()->create([
            'platform' => 'telegram',
            'platform_user_id' => '12345',
            'is_primary' => true,
        ]);

        // Set last_wager_joined_at to exactly 12 days ago
        $twelveAgo = \Carbon\Carbon::now()->startOfDay()->subDays(12);
        $twentyAgo = \Carbon\Carbon::now()->startOfDay()->subDays(20);
        
        DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->update([
                'last_wager_joined_at' => $twelveAgo->toDateTimeString(),
                'created_at' => $twentyAgo->toDateTimeString(),
            ]);

        // Apply decay check (should send warning, not apply decay)
        $results = $this->pointService->applyDecayForGroup($group);

        // Warning should be sent
        $this->assertEquals(1, $results['warnings_sent']);
        $this->assertEquals(0, $results['decay_applied']);

        // Check that warning timestamp was set
        $userGroup = DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->first();
        $this->assertNotNull($userGroup->decay_warning_sent_at);

        // Balance should remain unchanged
        $this->assertEquals(1000, $this->pointService->getBalance($user, $group));
    }

    public function test_decay_warning_not_sent_twice_in_same_day(): void
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $this->pointService->initializeUserPoints($user, $group);

        // Mock messenger service
        $user->messengerServices()->create([
            'platform' => 'telegram',
            'platform_user_id' => '12345',
            'is_primary' => true,
        ]);

        // Set last_wager_joined_at to exactly 12 days ago and warning already sent 10 hours ago
        DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->update([
                'last_wager_joined_at' => now()->subDays(12)->toDateTimeString(),
                'decay_warning_sent_at' => now()->subHours(10)->toDateTimeString(),
                'created_at' => now()->subDays(20)->toDateTimeString(),
            ]);

        // Apply decay check
        $results = $this->pointService->applyDecayForGroup($group);

        // Warning should NOT be sent again
        $this->assertEquals(0, $results['warnings_sent']);
        $this->assertEquals(0, $results['decay_applied']);
    }

    public function test_apply_decay_job_processes_multiple_groups(): void
    {
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->pointService->initializeUserPoints($user1, $group1);
        $this->pointService->initializeUserPoints($user2, $group2);

        // Both users inactive for 15 days
        DB::table('group_user')
            ->where('user_id', $user1->id)
            ->where('group_id', $group1->id)
            ->update([
                'last_wager_joined_at' => now()->subDays(15)->toDateTimeString(),
                'created_at' => now()->subDays(20)->toDateTimeString(),
            ]);

        DB::table('group_user')
            ->where('user_id', $user2->id)
            ->where('group_id', $group2->id)
            ->update([
                'last_wager_joined_at' => now()->subDays(15)->toDateTimeString(),
                'created_at' => now()->subDays(20)->toDateTimeString(),
            ]);

        // Run the job
        (new ApplyPointDecay())->handle($this->pointService);

        // Both users should have decay applied
        $this->assertEquals(950, $this->pointService->getBalance($user1, $group1));
        $this->assertEquals(950, $this->pointService->getBalance($user2, $group2));
    }

    public function test_decay_with_different_point_balances(): void
    {
        $group = Group::factory()->create();

        // Create users with different balances
        $lowUser = User::factory()->create();
        $midUser = User::factory()->create();
        $highUser = User::factory()->create();

        $this->pointService->initializeUserPoints($lowUser, $group);
        $this->pointService->initializeUserPoints($midUser, $group);
        $this->pointService->initializeUserPoints($highUser, $group);

        // Adjust balances
        DB::table('group_user')->where('user_id', $lowUser->id)->update(['points' => 1000]);
        DB::table('group_user')->where('user_id', $midUser->id)->update(['points' => 1500]);
        DB::table('group_user')->where('user_id', $highUser->id)->update(['points' => 5000]);

        // Set all as inactive for 15 days
        foreach ([$lowUser, $midUser, $highUser] as $user) {
            DB::table('group_user')
                ->where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->update([
                    'last_wager_joined_at' => now()->subDays(15)->toDateTimeString(),
                    'created_at' => now()->subDays(20)->toDateTimeString(),
                ]);
        }

        // Apply decay
        $this->pointService->applyDecayForGroup($group);

        // Check decay amounts
        $this->assertEquals(950, $this->pointService->getBalance($lowUser, $group));  // 1000 - 50 (min)
        $this->assertEquals(1425, $this->pointService->getBalance($midUser, $group)); // 1500 - 75 (5%)
        $this->assertEquals(4900, $this->pointService->getBalance($highUser, $group)); // 5000 - 100 (max)
    }
}
