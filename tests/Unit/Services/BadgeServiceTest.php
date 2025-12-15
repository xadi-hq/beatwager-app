<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\BadgeCategory;
use App\Enums\BadgeCriteriaType;
use App\Enums\BadgeTier;
use App\Events\BadgeAwarded;
use App\Events\BadgeRevoked;
use App\Models\Badge;
use App\Models\Group;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\Wager;
use App\Models\WagerEntry;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    private BadgeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BadgeService::class);
    }

    // =========================================================================
    // Award Tests
    // =========================================================================

    /** @test */
    public function it_awards_badge_to_user(): void
    {
        Event::fake([BadgeAwarded::class]);

        $user = User::factory()->create();
        $badge = Badge::factory()->create([
            'slug' => 'first_wager_won',
            'criteria_type' => BadgeCriteriaType::First,
            'criteria_event' => 'wager_won',
        ]);

        $userBadge = $this->service->award($user, $badge);

        $this->assertNotNull($userBadge);
        $this->assertEquals($user->id, $userBadge->user_id);
        $this->assertEquals($badge->id, $userBadge->badge_id);
        $this->assertNull($userBadge->revoked_at);

        Event::assertDispatched(BadgeAwarded::class, function ($event) use ($user, $badge) {
            return $event->user->id === $user->id && $event->badge->id === $badge->id;
        });
    }

    /** @test */
    public function it_does_not_award_duplicate_badge(): void
    {
        $user = User::factory()->create();
        $badge = Badge::factory()->create();

        // Award first time
        $this->service->award($user, $badge);

        // Try to award again
        $duplicate = $this->service->award($user, $badge);

        $this->assertNull($duplicate);
        $this->assertEquals(1, UserBadge::where('user_id', $user->id)->where('badge_id', $badge->id)->count());
    }

    /** @test */
    public function it_awards_same_badge_to_different_groups(): void
    {
        $user = User::factory()->create();
        $badge = Badge::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        $userBadge1 = $this->service->award($user, $badge, $group1);
        $userBadge2 = $this->service->award($user, $badge, $group2);

        $this->assertNotNull($userBadge1);
        $this->assertNotNull($userBadge2);
        $this->assertNotEquals($userBadge1->id, $userBadge2->id);
    }

    // =========================================================================
    // Revoke Tests
    // =========================================================================

    /** @test */
    public function it_revokes_badge(): void
    {
        Event::fake([BadgeRevoked::class]);

        $user = User::factory()->create();
        $badge = Badge::factory()->create();
        $userBadge = $this->service->award($user, $badge);

        $this->service->revoke($userBadge, 'Test revocation');

        $userBadge->refresh();
        $this->assertNotNull($userBadge->revoked_at);
        $this->assertEquals('Test revocation', $userBadge->revocation_reason);

        Event::assertDispatched(BadgeRevoked::class);
    }

    /** @test */
    public function it_does_not_revoke_already_revoked_badge(): void
    {
        Event::fake([BadgeRevoked::class]);

        $user = User::factory()->create();
        $badge = Badge::factory()->create();
        $userBadge = $this->service->award($user, $badge);

        // Revoke first time
        $this->service->revoke($userBadge, 'First revocation');
        $firstRevokedAt = $userBadge->fresh()->revoked_at;

        // Try to revoke again
        $this->service->revoke($userBadge, 'Second revocation');

        // Should still have first revocation timestamp
        $this->assertEquals($firstRevokedAt, $userBadge->fresh()->revoked_at);
        Event::assertDispatchedTimes(BadgeRevoked::class, 1);
    }

    // =========================================================================
    // Criteria Check Tests
    // =========================================================================

    /** @test */
    public function it_checks_first_criteria(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $badge = Badge::factory()->create([
            'criteria_type' => BadgeCriteriaType::First,
            'criteria_event' => 'wager_won',
        ]);

        // User has no wins yet
        $this->assertFalse($this->service->checkCriteria($badge, $user, $group));

        // Create a winning entry
        $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
        WagerEntry::factory()->create([
            'user_id' => $user->id,
            'wager_id' => $wager->id,
            'group_id' => $group->id,
            'is_winner' => true,
        ]);

        // Now should pass
        $this->assertTrue($this->service->checkCriteria($badge, $user, $group));
    }

    /** @test */
    public function it_checks_count_criteria(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $badge = Badge::factory()->create([
            'criteria_type' => BadgeCriteriaType::Count,
            'criteria_event' => 'wager_won',
            'criteria_threshold' => 3,
        ]);

        // Create 2 wins (below threshold)
        for ($i = 0; $i < 2; $i++) {
            $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
            WagerEntry::factory()->create([
                'user_id' => $user->id,
                'wager_id' => $wager->id,
                'group_id' => $group->id,
                'is_winner' => true,
            ]);
        }

        $this->assertFalse($this->service->checkCriteria($badge, $user, $group));

        // Add third win
        $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
        WagerEntry::factory()->create([
            'user_id' => $user->id,
            'wager_id' => $wager->id,
            'group_id' => $group->id,
            'is_winner' => true,
        ]);

        $this->assertTrue($this->service->checkCriteria($badge, $user, $group));
    }

    // =========================================================================
    // Check and Award Tests
    // =========================================================================

    /** @test */
    public function it_checks_and_awards_badges_for_event(): void
    {
        Event::fake([BadgeAwarded::class]);

        $user = User::factory()->create();
        $group = Group::factory()->create();

        // Create the badge
        $badge = Badge::factory()->create([
            'slug' => 'first_wager_won',
            'criteria_type' => BadgeCriteriaType::First,
            'criteria_event' => 'wager_won',
            'is_active' => true,
        ]);

        // Create a winning entry
        $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
        WagerEntry::factory()->create([
            'user_id' => $user->id,
            'wager_id' => $wager->id,
            'group_id' => $group->id,
            'is_winner' => true,
        ]);

        // Check and award
        $awarded = $this->service->checkAndAward($user, 'wager_won', $group, ['wager_id' => $wager->id]);

        $this->assertCount(1, $awarded);
        $this->assertEquals($badge->id, $awarded[0]->badge_id);
        Event::assertDispatched(BadgeAwarded::class);
    }

    /** @test */
    public function it_does_not_award_already_earned_badge(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $badge = Badge::factory()->create([
            'criteria_type' => BadgeCriteriaType::First,
            'criteria_event' => 'wager_won',
            'is_active' => true,
        ]);

        // Pre-award the badge
        $this->service->award($user, $badge, $group);

        // Create winning condition
        $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
        WagerEntry::factory()->create([
            'user_id' => $user->id,
            'wager_id' => $wager->id,
            'group_id' => $group->id,
            'is_winner' => true,
        ]);

        // Should not award again
        $awarded = $this->service->checkAndAward($user, 'wager_won', $group);

        $this->assertCount(0, $awarded);
    }

    // =========================================================================
    // Recheck After Reversal Tests
    // =========================================================================

    /** @test */
    public function it_revokes_badge_when_criteria_no_longer_met(): void
    {
        Event::fake([BadgeRevoked::class]);

        $user = User::factory()->create();
        $group = Group::factory()->create();

        $badge = Badge::factory()->create([
            'criteria_type' => BadgeCriteriaType::Count,
            'criteria_event' => 'wager_won',
            'criteria_threshold' => 3,
            'is_active' => true,
        ]);

        // Create 3 wins
        $entries = [];
        for ($i = 0; $i < 3; $i++) {
            $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
            $entries[] = WagerEntry::factory()->create([
                'user_id' => $user->id,
                'wager_id' => $wager->id,
                'group_id' => $group->id,
                'is_winner' => true,
            ]);
        }

        // Award the badge
        $this->service->award($user, $badge, $group);
        $this->assertTrue(UserBadge::exists($user->id, $badge->id, $group->id));

        // Simulate reversal - remove one win
        $entries[0]->update(['is_winner' => false, 'result' => 'lost']);

        // Recheck
        $revoked = $this->service->recheckAfterReversal($user, 'wager_won', $group, 'Dispute reversal');

        $this->assertCount(1, $revoked);
        $this->assertFalse(UserBadge::exists($user->id, $badge->id, $group->id));
        Event::assertDispatched(BadgeRevoked::class);
    }

    /** @test */
    public function it_does_not_revoke_first_type_badges(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $badge = Badge::factory()->create([
            'criteria_type' => BadgeCriteriaType::First,
            'criteria_event' => 'wager_won',
            'is_active' => true,
        ]);

        // Create a win and award badge
        $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
        $entry = WagerEntry::factory()->create([
            'user_id' => $user->id,
            'wager_id' => $wager->id,
            'group_id' => $group->id,
            'is_winner' => true,
        ]);

        $this->service->award($user, $badge, $group);

        // Remove the win
        $entry->update(['is_winner' => false]);

        // Recheck - should not revoke "first" type badges
        $revoked = $this->service->recheckAfterReversal($user, 'wager_won', $group);

        $this->assertCount(0, $revoked);
        $this->assertTrue(UserBadge::exists($user->id, $badge->id, $group->id));
    }

    // =========================================================================
    // Stat Tracking Tests
    // =========================================================================

    /** @test */
    public function it_counts_wagers_won_correctly(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        // Create 2 wins and 1 loss
        for ($i = 0; $i < 2; $i++) {
            $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
            WagerEntry::factory()->create([
                'user_id' => $user->id,
                'wager_id' => $wager->id,
                'group_id' => $group->id,
                'is_winner' => true,
            ]);
        }

        $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
        WagerEntry::factory()->create([
            'user_id' => $user->id,
            'wager_id' => $wager->id,
            'group_id' => $group->id,
            'is_winner' => false,
            'result' => 'lost',
        ]);

        $this->assertEquals(2, $this->service->getUserStat($user, 'wager_won', $group));
        $this->assertEquals(1, $this->service->getUserStat($user, 'wager_lost', $group));
    }

    /** @test */
    public function it_counts_wagers_created_correctly(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        Wager::factory()->count(3)->create([
            'creator_id' => $user->id,
            'group_id' => $group->id,
        ]);

        // Other user's wager
        Wager::factory()->create(['group_id' => $group->id]);

        $this->assertEquals(3, $this->service->getUserStat($user, 'wager_created', $group));
    }

    // =========================================================================
    // Get User Badges Tests
    // =========================================================================

    /** @test */
    public function it_gets_user_badges(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $badge1 = Badge::factory()->create();
        $badge2 = Badge::factory()->create();

        $this->service->award($user, $badge1, $group);
        $this->service->award($user, $badge2); // Global badge

        $badges = $this->service->getUserBadges($user, $group);

        $this->assertCount(2, $badges);
    }

    /** @test */
    public function it_excludes_revoked_badges_from_user_badges(): void
    {
        $user = User::factory()->create();
        $badge = Badge::factory()->create();

        $userBadge = $this->service->award($user, $badge);
        $this->service->revoke($userBadge, 'Test');

        $badges = $this->service->getUserBadges($user);

        $this->assertCount(0, $badges);
    }

    // =========================================================================
    // Badge Progress Tests
    // =========================================================================

    /** @test */
    public function it_calculates_badge_progress(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $badge = Badge::factory()->create([
            'criteria_type' => BadgeCriteriaType::Count,
            'criteria_event' => 'wager_won',
            'criteria_threshold' => 5,
        ]);

        // Create 2 wins
        for ($i = 0; $i < 2; $i++) {
            $wager = Wager::factory()->create(['group_id' => $group->id, 'status' => 'settled']);
            WagerEntry::factory()->create([
                'user_id' => $user->id,
                'wager_id' => $wager->id,
                'group_id' => $group->id,
                'is_winner' => true,
            ]);
        }

        $progress = $this->service->getBadgeProgress($user, $badge, $group);

        $this->assertFalse($progress['earned']);
        $this->assertEquals(2, $progress['current']);
        $this->assertEquals(5, $progress['threshold']);
        $this->assertEquals(40, $progress['progress_percent']);
    }
}
