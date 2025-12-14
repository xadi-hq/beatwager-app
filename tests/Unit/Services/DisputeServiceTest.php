<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Enums\DisputeVoteOutcome;
use App\Events\DisputeCreated;
use App\Events\DisputeResolved;
use App\Events\DisputeVoteReceived;
use App\Models\Dispute;
use App\Models\DisputeVote;
use App\Models\Group;
use App\Models\MessengerService;
use App\Models\User;
use App\Models\Wager;
use App\Services\DisputeService;
use App\Services\PointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class DisputeServiceTest extends TestCase
{
    use RefreshDatabase;

    private DisputeService $service;
    private MockInterface|PointService $pointService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pointService = Mockery::mock(PointService::class);
        $this->app->instance(PointService::class, $this->pointService);

        $this->service = app(DisputeService::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ========================================
    // createDispute Tests
    // ========================================

    /** @test */
    public function creates_dispute_for_settled_wager()
    {
        Event::fake([DisputeCreated::class]);

        $group = Group::factory()->create(['platform' => 'telegram']);
        $settler = User::factory()->create();
        $reporter = User::factory()->create();

        // Create a settled wager
        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'creator_id' => $settler->id,
            'status' => 'settled',
            'settled_at' => now()->subHour(),
            'outcome_value' => 'yes',
            'settler_id' => $settler->id,
        ]);

        $dispute = $this->service->createDispute($wager, $reporter);

        $this->assertInstanceOf(Dispute::class, $dispute);
        $this->assertEquals($group->id, $dispute->group_id);
        $this->assertEquals($wager->id, $dispute->disputable_id);
        $this->assertEquals(Wager::class, $dispute->disputable_type);
        $this->assertEquals($reporter->id, $dispute->reporter_id);
        $this->assertEquals($settler->id, $dispute->accused_id);
        $this->assertFalse($dispute->is_self_report);
        $this->assertEquals(DisputeStatus::Pending, $dispute->status);
        $this->assertEquals('yes', $dispute->original_outcome);
        $this->assertNotNull($dispute->expires_at);

        // Verify wager is linked to dispute
        $wager->refresh();
        $this->assertEquals($dispute->id, $wager->dispute_id);
        $this->assertEquals('disputed', $wager->status);

        Event::assertDispatched(DisputeCreated::class, fn($e) => $e->dispute->id === $dispute->id);
    }

    /** @test */
    public function creates_self_report_dispute()
    {
        Event::fake([DisputeCreated::class]);

        $group = Group::factory()->create(['platform' => 'telegram']);
        $settler = User::factory()->create();

        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'creator_id' => $settler->id,
            'status' => 'settled',
            'settled_at' => now()->subHour(),
            'outcome_value' => 'yes',
            'settler_id' => $settler->id,
        ]);

        // Settler reports their own mistake
        $dispute = $this->service->createDispute($wager, $settler);

        $this->assertTrue($dispute->is_self_report);
        $this->assertEquals($settler->id, $dispute->reporter_id);
        $this->assertEquals($settler->id, $dispute->accused_id);
    }

    /** @test */
    public function throws_exception_for_non_disputable_item()
    {
        $group = Group::factory()->create();
        $reporter = User::factory()->create();

        // Open wager cannot be disputed
        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'status' => 'open',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This item cannot be disputed.');

        $this->service->createDispute($wager, $reporter);
    }

    /** @test */
    public function throws_exception_for_already_disputed_item()
    {
        $group = Group::factory()->create(['platform' => 'telegram']);
        $settler = User::factory()->create();
        $reporter = User::factory()->create();

        // Create existing dispute
        $existingDispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'status' => DisputeStatus::Pending,
        ]);

        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'status' => 'disputed',
            'settled_at' => now()->subHour(),
            'settler_id' => $settler->id,
            'dispute_id' => $existingDispute->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This item cannot be disputed.');

        $this->service->createDispute($wager, $reporter);
    }

    // ========================================
    // castVote Tests
    // ========================================

    /** @test */
    public function casts_vote_on_dispute()
    {
        Event::fake([DisputeVoteReceived::class]);

        $group = Group::factory()->create(['platform' => 'telegram']);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter = User::factory()->create();

        // Attach voter to group
        $group->users()->attach($voter->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $dispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'reporter_id' => $reporter->id,
            'accused_id' => $accused->id,
            'status' => DisputeStatus::Pending,
            'votes_required' => 2,
            'expires_at' => now()->addHours(48),
        ]);

        $vote = $this->service->castVote(
            $dispute,
            $voter,
            DisputeVoteOutcome::OriginalCorrect
        );

        $this->assertInstanceOf(DisputeVote::class, $vote);
        $this->assertEquals($dispute->id, $vote->dispute_id);
        $this->assertEquals($voter->id, $vote->voter_id);
        $this->assertEquals(DisputeVoteOutcome::OriginalCorrect, $vote->vote_outcome);
        $this->assertNull($vote->selected_outcome);

        Event::assertDispatched(DisputeVoteReceived::class);
    }

    /** @test */
    public function casts_vote_with_selected_outcome()
    {
        Event::fake([DisputeVoteReceived::class]);

        $group = Group::factory()->create(['platform' => 'telegram']);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $dispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'reporter_id' => $reporter->id,
            'accused_id' => $accused->id,
            'status' => DisputeStatus::Pending,
            'votes_required' => 2,
            'expires_at' => now()->addHours(48),
        ]);

        $vote = $this->service->castVote(
            $dispute,
            $voter,
            DisputeVoteOutcome::DifferentOutcome,
            'no'
        );

        $this->assertEquals(DisputeVoteOutcome::DifferentOutcome, $vote->vote_outcome);
        $this->assertEquals('no', $vote->selected_outcome);
    }

    /** @test */
    public function throws_exception_when_voter_not_eligible()
    {
        $group = Group::factory()->create(['platform' => 'telegram']);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $dispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'reporter_id' => $reporter->id,
            'accused_id' => $accused->id,
            'status' => DisputeStatus::Pending,
            'expires_at' => now()->addHours(48),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You are not eligible to vote');

        // Reporter tries to vote (not allowed)
        $this->service->castVote($dispute, $reporter, DisputeVoteOutcome::OriginalCorrect);
    }

    /** @test */
    public function throws_exception_for_different_outcome_without_selection()
    {
        $group = Group::factory()->create(['platform' => 'telegram']);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $dispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'reporter_id' => $reporter->id,
            'accused_id' => $accused->id,
            'status' => DisputeStatus::Pending,
            'votes_required' => 2,
            'expires_at' => now()->addHours(48),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must specify the correct outcome');

        $this->service->castVote($dispute, $voter, DisputeVoteOutcome::DifferentOutcome);
    }

    // ========================================
    // Resolution Tests
    // ========================================

    /** @test */
    public function resolves_dispute_as_original_correct()
    {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(['platform' => 'telegram']);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'status' => 'disputed',
        ]);

        $dispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'disputable_type' => Wager::class,
            'disputable_id' => $wager->id,
            'reporter_id' => $reporter->id,
            'accused_id' => $accused->id,
            'status' => DisputeStatus::Pending,
        ]);

        $wager->update(['dispute_id' => $dispute->id]);

        // Expect false dispute penalty on reporter
        $this->pointService->shouldReceive('deductPercentage')
            ->once()
            ->with(
                Mockery::on(fn($u) => $u->id === $reporter->id),
                Mockery::type(Group::class),
                10,
                Mockery::type(\App\Enums\TransactionType::class),
                Mockery::type(Dispute::class)
            );

        $result = $this->service->resolveDispute($dispute, DisputeVoteOutcome::OriginalCorrect);

        $this->assertTrue($result);

        $dispute->refresh();
        $this->assertEquals(DisputeStatus::Resolved, $dispute->status);
        $this->assertEquals(DisputeResolution::OriginalCorrect, $dispute->resolution);
        $this->assertNotNull($dispute->resolved_at);

        // Wager should be cleared
        $wager->refresh();
        $this->assertNull($wager->dispute_id);

        Event::assertDispatched(DisputeResolved::class);
    }

    /** @test */
    public function resolves_dispute_as_fraud_confirmed()
    {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(['platform' => 'telegram']);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        // Create messenger service for accused (for fraud tracking)
        MessengerService::create([
            'user_id' => $accused->id,
            'platform' => 'telegram',
            'platform_user_id' => '12345',
            'fraud_offense_count' => 0,
        ]);

        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'status' => 'disputed',
            'outcome_value' => 'yes',
        ]);

        $dispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'disputable_type' => Wager::class,
            'disputable_id' => $wager->id,
            'reporter_id' => $reporter->id,
            'accused_id' => $accused->id,
            'status' => DisputeStatus::Pending,
            'original_outcome' => 'yes',
        ]);

        $wager->update(['dispute_id' => $dispute->id]);

        // Create vote with selected outcome
        DisputeVote::create([
            'dispute_id' => $dispute->id,
            'voter_id' => User::factory()->create()->id,
            'vote_outcome' => DisputeVoteOutcome::DifferentOutcome,
            'selected_outcome' => 'no',
        ]);

        // Expect fraud penalty on accused (25% for first offense)
        $this->pointService->shouldReceive('deductPercentage')
            ->once()
            ->with(
                Mockery::on(fn($u) => $u->id === $accused->id),
                Mockery::type(Group::class),
                25,
                Mockery::type(\App\Enums\TransactionType::class),
                Mockery::type(Dispute::class)
            );

        // Mock the WagerService for outcome correction
        $wagerService = Mockery::mock(\App\Services\WagerService::class);
        $wagerService->shouldReceive('reverseAndResettleWager')->once();
        $this->app->instance(\App\Services\WagerService::class, $wagerService);

        $result = $this->service->resolveDispute($dispute, DisputeVoteOutcome::DifferentOutcome);

        $this->assertTrue($result);

        $dispute->refresh();
        $this->assertEquals(DisputeResolution::FraudConfirmed, $dispute->resolution);
        $this->assertEquals('no', $dispute->corrected_outcome);
    }

    /** @test */
    public function resolves_self_report_with_honest_mistake_penalty()
    {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(['platform' => 'telegram']);
        $settler = User::factory()->create();

        MessengerService::create([
            'user_id' => $settler->id,
            'platform' => 'telegram',
            'platform_user_id' => '12345',
        ]);

        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'status' => 'disputed',
        ]);

        $dispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'disputable_type' => Wager::class,
            'disputable_id' => $wager->id,
            'reporter_id' => $settler->id,
            'accused_id' => $settler->id,
            'is_self_report' => true,
            'status' => DisputeStatus::Pending,
        ]);

        $wager->update(['dispute_id' => $dispute->id]);

        DisputeVote::create([
            'dispute_id' => $dispute->id,
            'voter_id' => User::factory()->create()->id,
            'vote_outcome' => DisputeVoteOutcome::DifferentOutcome,
            'selected_outcome' => 'no',
        ]);

        // Expect honest mistake penalty (5%) not fraud (25%)
        $this->pointService->shouldReceive('deductPercentage')
            ->once()
            ->with(
                Mockery::on(fn($u) => $u->id === $settler->id),
                Mockery::type(Group::class),
                5,
                \App\Enums\TransactionType::DisputePenaltyHonestMistake,
                Mockery::type(Dispute::class)
            );

        $wagerService = Mockery::mock(\App\Services\WagerService::class);
        $wagerService->shouldReceive('reverseAndResettleWager')->once();
        $this->app->instance(\App\Services\WagerService::class, $wagerService);

        $this->service->resolveDispute($dispute, DisputeVoteOutcome::DifferentOutcome);
    }

    // ========================================
    // Expiration Tests
    // ========================================

    /** @test */
    public function handles_expired_dispute_with_no_votes()
    {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(['platform' => 'telegram']);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'status' => 'disputed',
        ]);

        $dispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'disputable_type' => Wager::class,
            'disputable_id' => $wager->id,
            'reporter_id' => $reporter->id,
            'accused_id' => $accused->id,
            'status' => DisputeStatus::Pending,
            'expires_at' => now()->subHour(), // Already expired
        ]);

        $wager->update(['dispute_id' => $dispute->id]);

        // No penalty when nobody voted - that's not a "false" dispute
        $this->pointService->shouldNotReceive('deductPercentage');

        $this->service->handleExpiredDispute($dispute);

        $dispute->refresh();
        $this->assertEquals(DisputeStatus::Resolved, $dispute->status);
        $this->assertEquals(DisputeResolution::OriginalCorrect, $dispute->resolution);

        $wager->refresh();
        $this->assertEquals('settled', $wager->status);
        $this->assertNull($wager->dispute_id);

        Event::assertDispatched(DisputeResolved::class);
    }

    /** @test */
    public function handles_expired_dispute_with_votes()
    {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(['platform' => 'telegram']);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter = User::factory()->create();

        $wager = Wager::factory()->create([
            'group_id' => $group->id,
            'status' => 'disputed',
        ]);

        $dispute = Dispute::factory()->create([
            'group_id' => $group->id,
            'disputable_type' => Wager::class,
            'disputable_id' => $wager->id,
            'reporter_id' => $reporter->id,
            'accused_id' => $accused->id,
            'status' => DisputeStatus::Pending,
            'votes_required' => 1,
            'expires_at' => now()->subHour(),
        ]);

        $wager->update(['dispute_id' => $dispute->id]);

        // Add a vote for original correct
        DisputeVote::create([
            'dispute_id' => $dispute->id,
            'voter_id' => $voter->id,
            'vote_outcome' => DisputeVoteOutcome::OriginalCorrect,
        ]);

        $this->pointService->shouldReceive('deductPercentage')->once();

        $this->service->handleExpiredDispute($dispute);

        $dispute->refresh();
        $this->assertEquals(DisputeStatus::Resolved, $dispute->status);
        $this->assertEquals(DisputeResolution::OriginalCorrect, $dispute->resolution);
    }

    // ========================================
    // Votes Required Calculation Tests
    // ========================================

    /** @test */
    public function calculates_votes_required_for_small_group()
    {
        $group = Group::factory()->create();
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $member = User::factory()->create();

        // 3 members total (1 eligible voter after excluding reporter and accused)
        $group->users()->attach($reporter->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000]);
        $group->users()->attach($accused->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000]);
        $group->users()->attach($member->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000]);

        $votesRequired = Dispute::calculateVotesRequired($group, $reporter, $accused);

        $this->assertEquals(1, $votesRequired);
    }

    /** @test */
    public function calculates_votes_required_for_large_group()
    {
        $group = Group::factory()->create();
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        // 5 members total (3 eligible voters)
        $group->users()->attach($reporter->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000]);
        $group->users()->attach($accused->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000]);

        for ($i = 0; $i < 3; $i++) {
            $member = User::factory()->create();
            $group->users()->attach($member->id, ['id' => \Illuminate\Support\Str::uuid(), 'points' => 1000]);
        }

        $votesRequired = Dispute::calculateVotesRequired($group, $reporter, $accused);

        $this->assertEquals(2, $votesRequired);
    }
}
