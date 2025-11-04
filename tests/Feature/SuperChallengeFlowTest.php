<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ChallengeType;
use App\Enums\NudgeResponse;
use App\Enums\SuperChallengeFrequency;
use App\Enums\ValidationStatus;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Group;
use App\Models\SuperChallengeNudge;
use App\Models\User;
use App\Services\SuperChallengeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperChallengeFlowTest extends TestCase
{
    use RefreshDatabase;

    private SuperChallengeService $service;
    private Group $group;
    private User $creator;
    private User $participant1;
    private User $participant2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SuperChallengeService::class);

        // Create test group
        $this->group = Group::factory()->create([
            'starting_balance' => 10000,
            'superchallenge_frequency' => SuperChallengeFrequency::MONTHLY->value,
            'last_superchallenge_at' => null,
        ]);

        // Create users
        $this->creator = User::factory()->create(['name' => 'Creator']);
        $this->participant1 = User::factory()->create(['name' => 'Alice']);
        $this->participant2 = User::factory()->create(['name' => 'Bob']);

        // Attach to group
        foreach ([$this->creator, $this->participant1, $this->participant2] as $user) {
            $this->group->users()->attach($user->id, [
                'id' => Str::uuid(),
                'points' => 1000,
                'role' => 'participant',
            ]);
        }
    }

    /** @test */
    public function complete_superchallenge_lifecycle_with_approval(): void
    {
        // STEP 1: Create nudge for creator explicitly (deterministic test)
        $nudge = SuperChallengeNudge::create([
            'id' => Str::uuid(),
            'group_id' => $this->group->id,
            'nudged_user_id' => $this->creator->id,
            'response' => NudgeResponse::PENDING,
            'nudged_at' => now(),
        ]);

        // STEP 2: Nudged user creates SuperChallenge
        $challenge = $this->service->createSuperChallenge(
            nudge: $nudge,
            description: 'Meditate for 10 minutes',
            deadlineInDays: 7,
            prizePerPerson: 100,
            maxParticipants: 5,
            evidenceGuidance: 'Post a photo or describe your experience'
        );

        $this->assertInstanceOf(Challenge::class, $challenge);
        $this->assertEquals(ChallengeType::SUPER_CHALLENGE, $challenge->type);
        $this->assertEquals('Meditate for 10 minutes', $challenge->description);

        $nudge->refresh();
        $this->assertEquals(NudgeResponse::ACCEPTED, $nudge->response);

        $this->group->refresh();
        $this->assertNotNull($this->group->last_superchallenge_at);

        // STEP 3: Participants accept the challenge
        $creatorPointsBefore = $this->creator->groupMembership($this->group->id)->points;

        $participant1Entry = $this->service->acceptChallenge($challenge, $this->participant1);
        $participant2Entry = $this->service->acceptChallenge($challenge, $this->participant2);

        $this->assertNotNull($participant1Entry->accepted_at);
        $this->assertNotNull($participant2Entry->accepted_at);
        $this->assertEquals(2, $challenge->participants()->count());

        // STEP 4: Verify acceptance bonus was awarded automatically to creator (on first acceptance)
        $this->creator->refresh();
        $creatorPointsAfter = $this->creator->groupMembership($this->group->id)->points;
        // Dynamic formula: (150 - prize_per_person) + base_bonus
        // With prize_per_person = 100: (150 - 100) + 50 = 100
        $this->assertEquals($creatorPointsBefore + 100, $creatorPointsAfter);

        // STEP 5: Participants claim completion
        $participant1Entry = $this->service->claimCompletion($challenge, $this->participant1);
        $participant2Entry = $this->service->claimCompletion($challenge, $this->participant2);

        $this->assertNotNull($participant1Entry->completed_at);
        $this->assertNotNull($participant2Entry->completed_at);
        $this->assertEquals(ValidationStatus::PENDING, $participant1Entry->validation_status);
        $this->assertEquals(ValidationStatus::PENDING, $participant2Entry->validation_status);

        // STEP 6: Creator validates completions
        $participant1PointsBefore = $this->participant1->groupMembership($this->group->id)->points;
        $participant2PointsBefore = $this->participant2->groupMembership($this->group->id)->points;
        $creatorPointsBeforeValidation = $this->creator->groupMembership($this->group->id)->points;

        // Approve participant 1
        $this->service->validateCompletion($participant1Entry, $this->creator, approve: true);

        $participant1Entry->refresh();
        $this->assertEquals(ValidationStatus::VALIDATED, $participant1Entry->validation_status);
        $this->assertNotNull($participant1Entry->validated_by_creator_at);

        // Approve participant 2
        $this->service->validateCompletion($participant2Entry, $this->creator, approve: true);

        $participant2Entry->refresh();
        $this->assertEquals(ValidationStatus::VALIDATED, $participant2Entry->validation_status);

        // STEP 7: Verify prize distribution
        $this->participant1->refresh();
        $this->participant2->refresh();
        $this->creator->refresh();

        $participant1PointsAfter = $this->participant1->groupMembership($this->group->id)->points;
        $participant2PointsAfter = $this->participant2->groupMembership($this->group->id)->points;
        $creatorPointsAfterValidation = $this->creator->groupMembership($this->group->id)->points;

        // Each participant gets full prize
        $this->assertEquals($participant1PointsBefore + $challenge->prize_per_person, $participant1PointsAfter);
        $this->assertEquals($participant2PointsBefore + $challenge->prize_per_person, $participant2PointsAfter);

        // Creator gets validation bonus for each approval (+25 × 2 = +50)
        $this->assertEquals($creatorPointsBeforeValidation + 50, $creatorPointsAfterValidation);
    }

    /** @test */
    public function complete_superchallenge_lifecycle_with_rejection(): void
    {
        // Create challenge
        $nudge = SuperChallengeNudge::factory()->create([
            'group_id' => $this->group->id,
            'nudged_user_id' => $this->creator->id,
        ]);

        $challenge = $this->service->createSuperChallenge(
            nudge: $nudge,
            description: 'Run 5km under 30 minutes',
            deadlineInDays: 7,
            prizePerPerson: 100,
            maxParticipants: 5,
            evidenceGuidance: 'Screenshot from running app showing time and distance'
        );

        // Participant accepts
        $participant = $this->service->acceptChallenge($challenge, $this->participant1);

        // Participant claims completion
        $participant = $this->service->claimCompletion($challenge, $this->participant1);

        $participantPointsBefore = $this->participant1->groupMembership($this->group->id)->points;

        // Creator rejects completion
        $this->service->validateCompletion($participant, $this->creator, approve: false);

        $participant->refresh();
        $this->assertEquals(ValidationStatus::REJECTED, $participant->validation_status);
        $this->assertNotNull($participant->validated_by_creator_at);
        $this->assertNull($participant->prize_transaction_id);

        // Participant should NOT receive prize
        $this->participant1->refresh();
        $participantPointsAfter = $this->participant1->groupMembership($this->group->id)->points;
        $this->assertEquals($participantPointsBefore, $participantPointsAfter);
    }

    /** @test */
    public function complete_superchallenge_lifecycle_with_auto_approval(): void
    {
        // Create challenge
        $nudge = SuperChallengeNudge::factory()->create([
            'group_id' => $this->group->id,
            'nudged_user_id' => $this->creator->id,
        ]);

        $challenge = $this->service->createSuperChallenge(
            nudge: $nudge,
            description: 'Read a book',
            deadlineInDays: 14,
            prizePerPerson: 100,
            maxParticipants: 5
        );

        // Participant accepts
        $participant = $this->service->acceptChallenge($challenge, $this->participant1);

        // Participant claims completion 49 hours ago
        $participant->update([
            'completed_at' => now()->subHours(49),
            'validation_status' => ValidationStatus::PENDING,
        ]);

        $participantPointsBefore = $this->participant1->groupMembership($this->group->id)->points;

        // Process auto-approvals
        $this->service->processAutoApprovals();

        $participant->refresh();
        $this->assertEquals(ValidationStatus::VALIDATED, $participant->validation_status);
        $this->assertNotNull($participant->auto_validated_at);
        $this->assertNotNull($participant->prize_transaction_id);

        // Participant should receive prize
        $this->participant1->refresh();
        $participantPointsAfter = $this->participant1->groupMembership($this->group->id)->points;
        $this->assertEquals($participantPointsBefore + $challenge->prize_per_person, $participantPointsAfter);
    }

    /** @test */
    public function nudge_can_be_declined(): void
    {
        // Process eligible groups
        $this->service->processEligibleGroups();

        $nudge = SuperChallengeNudge::where('group_id', $this->group->id)->first();
        $this->assertEquals(NudgeResponse::PENDING, $nudge->response);

        // User declines
        $nudge->update([
            'response' => NudgeResponse::DECLINED,
            'responded_at' => now(),
        ]);

        $nudge->refresh();
        $this->assertEquals(NudgeResponse::DECLINED, $nudge->response);
        $this->assertNotNull($nudge->responded_at);

        // Group's last_superchallenge_at should NOT be updated
        $this->group->refresh();
        $this->assertNull($this->group->last_superchallenge_at);
    }

    /** @test */
    public function max_participants_limit_is_enforced(): void
    {
        $nudge = SuperChallengeNudge::factory()->create([
            'group_id' => $this->group->id,
            'nudged_user_id' => $this->creator->id,
        ]);

        $challenge = $this->service->createSuperChallenge(
            nudge: $nudge,
            description: 'Test Challenge',
            deadlineInDays: 7,
            prizePerPerson: 100,
            maxParticipants: 2  // Test with only 2 participants to test the limit
        );

        // Manually set max to 1 for testing
        $challenge->update(['max_participants' => 1]);

        // First participant accepts successfully
        $this->service->acceptChallenge($challenge, $this->participant1);

        // Second participant tries to accept - should fail
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Challenge has reached maximum participants');

        $this->service->acceptChallenge($challenge, $this->participant2);
    }

    /** @test */
    public function creator_cannot_accept_their_own_challenge(): void
    {
        $nudge = SuperChallengeNudge::factory()->create([
            'group_id' => $this->group->id,
            'nudged_user_id' => $this->creator->id,
        ]);

        $challenge = $this->service->createSuperChallenge(
            nudge: $nudge,
            description: 'Test Challenge',
            deadlineInDays: 7,
            prizePerPerson: 100,
            maxParticipants: 5
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Creator cannot accept their own SuperChallenge');

        $this->service->acceptChallenge($challenge, $this->creator);
    }

    /** @test */
    public function participant_cannot_accept_same_challenge_twice(): void
    {
        $nudge = SuperChallengeNudge::factory()->create([
            'group_id' => $this->group->id,
            'nudged_user_id' => $this->creator->id,
        ]);

        $challenge = $this->service->createSuperChallenge(
            nudge: $nudge,
            description: 'Test Challenge',
            deadlineInDays: 7,
            prizePerPerson: 100,
            maxParticipants: 5
        );

        // First acceptance
        $this->service->acceptChallenge($challenge, $this->participant1);

        // Second acceptance attempt
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User has already accepted this challenge');

        $this->service->acceptChallenge($challenge, $this->participant1);
    }

    /** @test */
    public function weekly_frequency_groups_are_eligible_after_7_days(): void
    {
        Event::fake();

        $this->group->update([
            'superchallenge_frequency' => SuperChallengeFrequency::WEEKLY->value,
            'last_superchallenge_at' => now()->subDays(8),
        ]);

        $this->service->processEligibleGroups();

        // Should create nudge
        $this->assertDatabaseHas('super_challenge_nudges', [
            'group_id' => $this->group->id,
        ]);
    }

    /** @test */
    public function quarterly_frequency_groups_are_eligible_after_3_months(): void
    {
        Event::fake();

        $this->group->update([
            'superchallenge_frequency' => SuperChallengeFrequency::QUARTERLY->value,
            'last_superchallenge_at' => now()->subMonths(3)->subDay(),
        ]);

        $this->service->processEligibleGroups();

        // Should create nudge
        $this->assertDatabaseHas('super_challenge_nudges', [
            'group_id' => $this->group->id,
        ]);
    }

    /** @test */
    public function groups_with_less_than_3_members_are_not_eligible(): void
    {
        Event::fake();

        // Remove all but 2 members
        $this->group->users()->detach($this->participant2->id);

        $this->assertEquals(2, $this->group->users()->count());

        $this->service->processEligibleGroups();

        // Should NOT create nudge
        $this->assertDatabaseMissing('super_challenge_nudges', [
            'group_id' => $this->group->id,
        ]);
    }
}
