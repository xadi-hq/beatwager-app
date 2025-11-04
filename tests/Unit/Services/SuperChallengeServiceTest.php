<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\ChallengeType;
use App\Enums\NudgeResponse;
use App\Enums\SuperChallengeFrequency;
use App\Enums\ValidationStatus;
use App\Events\SuperChallengeAccepted;
use App\Events\SuperChallengeAutoValidated;
use App\Events\SuperChallengeCompletionClaimed;
use App\Events\SuperChallengeCompletionValidated;
use App\Events\SuperChallengeCreated;
use App\Events\SuperChallengeNudgeSent;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Group;
use App\Models\SuperChallengeNudge;
use App\Models\User;
use App\Services\PointService;
use App\Services\SuperChallengeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperChallengeServiceTest extends TestCase
{
    use RefreshDatabase;

    private SuperChallengeService $service;
    private PointService $pointService;
    private Group $group;
    private User $creator;
    private User $participant;
    private User $participant2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pointService = app(PointService::class);
        $this->service = new SuperChallengeService($this->pointService);

        // Create test data
        $this->group = Group::factory()->create([
            'starting_balance' => 10000,
            'superchallenge_frequency' => SuperChallengeFrequency::MONTHLY->value,
            'last_superchallenge_at' => null,
        ]);

        $this->creator = User::factory()->create(['name' => 'Creator']);
        $this->participant = User::factory()->create(['name' => 'Participant']);
        $this->participant2 = User::factory()->create(['name' => 'Participant 2']);

        // Attach users to group with points
        foreach ([$this->creator, $this->participant, $this->participant2] as $user) {
            $this->group->users()->attach($user->id, [
                'id' => Str::uuid(),
                'points' => 1000,
                'role' => 'participant',
            ]);
        }
    }

    /** @test */
    public function it_processes_eligible_groups_and_sends_nudges(): void
    {
        Event::fake([SuperChallengeNudgeSent::class]);

        // Group is eligible (no previous SuperChallenge)
        $this->assertNull($this->group->last_superchallenge_at);

        $this->service->processEligibleGroups();

        // Check nudge was created
        $nudge = SuperChallengeNudge::where('group_id', $this->group->id)->first();
        $this->assertNotNull($nudge);
        $this->assertEquals(NudgeResponse::PENDING, $nudge->response);
        $this->assertContains($nudge->nudged_user_id, [$this->creator->id, $this->participant->id, $this->participant2->id]);

        // Event was dispatched
        Event::assertDispatched(SuperChallengeNudgeSent::class);
    }

    /** @test */
    public function it_does_not_process_groups_with_recent_superchallenges(): void
    {
        Event::fake([SuperChallengeNudgeSent::class]);

        // Group had SuperChallenge 2 weeks ago (monthly frequency = not eligible yet)
        $this->group->update(['last_superchallenge_at' => now()->subWeeks(2)]);

        $this->service->processEligibleGroups();

        // No nudge should be created
        $this->assertDatabaseCount('super_challenge_nudges', 0);
        Event::assertNotDispatched(SuperChallengeNudgeSent::class);
    }

    /** @test */
    public function it_creates_superchallenge_with_correct_prize_structure(): void
    {
        Event::fake([SuperChallengeCreated::class]);

        $nudge = SuperChallengeNudge::factory()->create([
            'group_id' => $this->group->id,
            'nudged_user_id' => $this->creator->id,
        ]);

        $challenge = $this->service->createSuperChallenge(
            nudge: $nudge,
            description: 'Run 5km',
            deadlineInDays: 7,
            evidenceGuidance: 'Post a screenshot from your running app'
        );

        $this->assertInstanceOf(Challenge::class, $challenge);
        $this->assertEquals(ChallengeType::SUPER_CHALLENGE, $challenge->type);
        $this->assertEquals('Run 5km', $challenge->description);
        $this->assertEquals($this->creator->id, $challenge->creator_id);
        $this->assertEquals($this->group->id, $challenge->group_id);

        // Prize structure checks
        $this->assertGreaterThanOrEqual(50, $challenge->prize_per_person);
        $this->assertLessThanOrEqual(150, $challenge->prize_per_person);
        $this->assertGreaterThanOrEqual(1, $challenge->max_participants);
        $this->assertLessThanOrEqual(10, $challenge->max_participants);

        // Prize should be multiple of 50
        $this->assertEquals(0, $challenge->prize_per_person % 50);

        // Deadline check
        $expectedDeadline = now()->addDays(7);
        $this->assertEquals($expectedDeadline->format('Y-m-d'), $challenge->completion_deadline->format('Y-m-d'));

        // Nudge should be marked as accepted
        $nudge->refresh();
        $this->assertEquals(NudgeResponse::ACCEPTED, $nudge->response);
        $this->assertNotNull($nudge->responded_at);

        // Group's last_superchallenge_at should be updated
        $this->group->refresh();
        $this->assertNotNull($this->group->last_superchallenge_at);

        Event::assertDispatched(SuperChallengeCreated::class);
    }

    /** @test */
    public function it_accepts_challenge_for_participant(): void
    {
        Event::fake([SuperChallengeAccepted::class]);

        $challenge = Challenge::factory()->create([
            'type' => ChallengeType::SUPER_CHALLENGE,
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'prize_per_person' => 100,
            'max_participants' => 5,
        ]);

        $participant = $this->service->acceptChallenge($challenge, $this->participant);

        $this->assertInstanceOf(ChallengeParticipant::class, $participant);
        $this->assertEquals($challenge->id, $participant->challenge_id);
        $this->assertEquals($this->participant->id, $participant->user_id);
        $this->assertNotNull($participant->accepted_at);
        $this->assertNull($participant->completed_at);
        $this->assertEquals(ValidationStatus::PENDING, $participant->validation_status);

        Event::assertDispatched(SuperChallengeAccepted::class);
    }

    /** @test */
    public function it_prevents_creator_from_accepting_own_challenge(): void
    {
        $challenge = Challenge::factory()->create([
            'type' => ChallengeType::SUPER_CHALLENGE,
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'prize_per_person' => 100,
            'max_participants' => 5,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Creator cannot accept their own SuperChallenge');

        $this->service->acceptChallenge($challenge, $this->creator);
    }

    /** @test */
    public function it_prevents_accepting_when_max_participants_reached(): void
    {
        $challenge = Challenge::factory()->create([
            'type' => ChallengeType::SUPER_CHALLENGE,
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'prize_per_person' => 100,
            'max_participants' => 1,
        ]);

        // First participant accepts
        $this->service->acceptChallenge($challenge, $this->participant);

        // Try to add another participant
        $newUser = User::factory()->create();
        $this->group->users()->attach($newUser->id, [
            'id' => Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Challenge has reached maximum participants');

        $this->service->acceptChallenge($challenge, $newUser);
    }

    /** @test */
    public function it_awards_acceptance_bonus_to_creator_on_first_acceptance(): void
    {
        $challenge = Challenge::factory()->create([
            'type' => ChallengeType::SUPER_CHALLENGE,
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'prize_per_person' => 100,
            'max_participants' => 5,
        ]);

        $creatorPointsBefore = $this->creator->groupMembership($this->group->id)->points;

        // Award acceptance bonus
        $this->service->awardAcceptanceBonus($challenge);

        $this->creator->refresh();
        $creatorPointsAfter = $this->creator->groupMembership($this->group->id)->points;

        $this->assertEquals($creatorPointsBefore + 50, $creatorPointsAfter);
    }

    /** @test */
    public function it_claims_completion_for_participant(): void
    {
        Event::fake([SuperChallengeCompletionClaimed::class]);

        $challenge = Challenge::factory()->create([
            'type' => ChallengeType::SUPER_CHALLENGE,
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'prize_per_person' => 100,
        ]);

        $participant = ChallengeParticipant::factory()->create([
            'challenge_id' => $challenge->id,
            'user_id' => $this->participant->id,
            'accepted_at' => now(),
        ]);

        $result = $this->service->claimCompletion($challenge, $this->participant);

        $this->assertNotNull($result->completed_at);
        $this->assertEquals(ValidationStatus::PENDING, $result->validation_status);

        Event::assertDispatched(SuperChallengeCompletionClaimed::class);
    }

    /** @test */
    public function it_validates_completion_with_approval(): void
    {
        Event::fake([SuperChallengeCompletionValidated::class]);

        $challenge = Challenge::factory()->create([
            'type' => ChallengeType::SUPER_CHALLENGE,
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'prize_per_person' => 100,
        ]);

        $participant = ChallengeParticipant::factory()->create([
            'challenge_id' => $challenge->id,
            'user_id' => $this->participant->id,
            'accepted_at' => now(),
            'completed_at' => now(),
            'validation_status' => ValidationStatus::PENDING,
        ]);

        $participantPointsBefore = $this->participant->groupMembership($this->group->id)->points;
        $creatorPointsBefore = $this->creator->groupMembership($this->group->id)->points;

        $this->service->validateCompletion($participant, $this->creator, approve: true);

        $participant->refresh();
        $this->assertEquals(ValidationStatus::VALIDATED, $participant->validation_status);
        $this->assertNotNull($participant->validated_by_creator_at);
        $this->assertNotNull($participant->prize_transaction_id);

        // Participant should receive prize
        $this->participant->refresh();
        $participantPointsAfter = $this->participant->groupMembership($this->group->id)->points;
        $this->assertEquals($participantPointsBefore + 100, $participantPointsAfter);

        // Creator should receive validation bonus
        $this->creator->refresh();
        $creatorPointsAfter = $this->creator->groupMembership($this->group->id)->points;
        $this->assertEquals($creatorPointsBefore + 25, $creatorPointsAfter);

        Event::assertDispatched(SuperChallengeCompletionValidated::class);
    }

    /** @test */
    public function it_validates_completion_with_rejection(): void
    {
        Event::fake([SuperChallengeCompletionValidated::class]);

        $challenge = Challenge::factory()->create([
            'type' => ChallengeType::SUPER_CHALLENGE,
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'prize_per_person' => 100,
        ]);

        $participant = ChallengeParticipant::factory()->create([
            'challenge_id' => $challenge->id,
            'user_id' => $this->participant->id,
            'accepted_at' => now(),
            'completed_at' => now(),
            'validation_status' => ValidationStatus::PENDING,
        ]);

        $participantPointsBefore = $this->participant->groupMembership($this->group->id)->points;

        $this->service->validateCompletion($participant, $this->creator, approve: false);

        $participant->refresh();
        $this->assertEquals(ValidationStatus::REJECTED, $participant->validation_status);
        $this->assertNotNull($participant->validated_by_creator_at);
        $this->assertNull($participant->prize_transaction_id);

        // Participant should NOT receive prize
        $this->participant->refresh();
        $participantPointsAfter = $this->participant->groupMembership($this->group->id)->points;
        $this->assertEquals($participantPointsBefore, $participantPointsAfter);

        Event::assertDispatched(SuperChallengeCompletionValidated::class);
    }

    /** @test */
    public function it_auto_approves_pending_completions_after_48_hours(): void
    {
        Event::fake([SuperChallengeAutoValidated::class]);

        $challenge = Challenge::factory()->create([
            'type' => ChallengeType::SUPER_CHALLENGE,
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'prize_per_person' => 100,
        ]);

        $participant = ChallengeParticipant::factory()->create([
            'challenge_id' => $challenge->id,
            'user_id' => $this->participant->id,
            'accepted_at' => now()->subDays(3),
            'completed_at' => now()->subHours(49),
            'validation_status' => ValidationStatus::PENDING,
        ]);

        $participantPointsBefore = $this->participant->groupMembership($this->group->id)->points;

        $this->service->processAutoApprovals();

        $participant->refresh();
        $this->assertEquals(ValidationStatus::VALIDATED, $participant->validation_status);
        $this->assertNotNull($participant->auto_validated_at);
        $this->assertNotNull($participant->prize_transaction_id);

        // Participant should receive prize
        $this->participant->refresh();
        $participantPointsAfter = $this->participant->groupMembership($this->group->id)->points;
        $this->assertEquals($participantPointsBefore + 100, $participantPointsAfter);

        Event::assertDispatched(SuperChallengeAutoValidated::class);
    }

    /** @test */
    public function it_does_not_auto_approve_completions_before_48_hours(): void
    {
        Event::fake([SuperChallengeAutoValidated::class]);

        $challenge = Challenge::factory()->create([
            'type' => ChallengeType::SUPER_CHALLENGE,
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'prize_per_person' => 100,
        ]);

        $participant = ChallengeParticipant::factory()->create([
            'challenge_id' => $challenge->id,
            'user_id' => $this->participant->id,
            'accepted_at' => now()->subDays(1),
            'completed_at' => now()->subHours(24), // Only 24 hours ago
            'validation_status' => ValidationStatus::PENDING,
        ]);

        $this->service->processAutoApprovals();

        $participant->refresh();
        $this->assertEquals(ValidationStatus::PENDING, $participant->validation_status);
        $this->assertNull($participant->auto_validated_at);

        Event::assertNotDispatched(SuperChallengeAutoValidated::class);
    }

    /** @test */
    public function it_calculates_prize_based_on_group_points(): void
    {
        // Group with 10,000 total points (update pivot table directly)
        foreach ([$this->creator, $this->participant, $this->participant2] as $user) {
            \DB::table('group_user')
                ->where('user_id', $user->id)
                ->where('group_id', $this->group->id)
                ->update(['points' => 5000]);
        }

        $nudge = SuperChallengeNudge::factory()->create([
            'group_id' => $this->group->id,
            'nudged_user_id' => $this->creator->id,
        ]);

        $challenge = $this->service->createSuperChallenge(
            nudge: $nudge,
            description: 'Test Challenge',
            deadlineInDays: 7
        );

        // Prize should be 2-5% of 15,000 (300-750) divided by 50 and rounded
        // Then capped at 50-150 range
        $this->assertGreaterThanOrEqual(50, $challenge->prize_per_person);
        $this->assertLessThanOrEqual(150, $challenge->prize_per_person);
    }

    /** @test */
    public function it_limits_max_participants_based_on_total_prize_cap(): void
    {
        $nudge = SuperChallengeNudge::factory()->create([
            'group_id' => $this->group->id,
            'nudged_user_id' => $this->creator->id,
        ]);

        $challenge = $this->service->createSuperChallenge(
            nudge: $nudge,
            description: 'Test Challenge',
            deadlineInDays: 7
        );

        // Max total prize is 1000
        // If prize_per_person is 150, max_participants should be 6 (150 × 6 = 900)
        // If prize_per_person is 50, max_participants should be 10 (capped)
        $totalPrize = $challenge->prize_per_person * $challenge->max_participants;
        $this->assertLessThanOrEqual(1000, $totalPrize);
        $this->assertLessThanOrEqual(10, $challenge->max_participants);
    }
}
