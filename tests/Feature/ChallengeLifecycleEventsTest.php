<?php

namespace Tests\Feature;

use App\Events\ChallengeAccepted;
use App\Events\ChallengeApproved;
use App\Events\ChallengeCancelled;
use App\Events\ChallengeCreated;
use App\Events\ChallengeDeadlineMissed;
use App\Events\ChallengeExpired;
use App\Events\ChallengeRejected;
use App\Events\ChallengeSubmitted;
use App\Models\Challenge;
use App\Models\Group;
use App\Models\User;
use App\Services\ChallengeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChallengeLifecycleEventsTest extends TestCase
{
    use RefreshDatabase;

    private User $creator;
    private User $acceptor;
    private Group $group;
    private ChallengeService $challengeService;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake events to prevent actual message sending
        Event::fake();

        // Create users
        $this->creator = User::factory()->create();
        $this->acceptor = User::factory()->create();

        // Create group with starting balance
        $this->group = Group::factory()->create(['starting_balance' => 1000]);
        $this->group->users()->attach($this->creator->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'admin',
        ]);
        $this->group->users()->attach($this->acceptor->id, [
            'id' => \Illuminate\Support\Str::uuid(),
            'points' => 1000,
            'role' => 'participant',
        ]);

        $this->challengeService = app(ChallengeService::class);
    }

    /** @test */
    public function it_dispatches_challenge_created_event_when_challenge_is_created()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => 100,
        ]);

        \App\Events\ChallengeCreated::dispatch($challenge);

        Event::assertDispatched(ChallengeCreated::class, function ($event) use ($challenge) {
            return $event->challenge->id === $challenge->id;
        });
    }

    /** @test */
    public function it_dispatches_challenge_accepted_event_when_challenge_is_accepted()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => 100,
            'status' => 'open',
        ]);

        $acceptedChallenge = $this->challengeService->acceptChallenge($challenge, $this->acceptor);

        Event::assertDispatched(ChallengeAccepted::class, function ($event) use ($acceptedChallenge) {
            return $event->challenge->id === $acceptedChallenge->id
                && $event->acceptor->id === $this->acceptor->id;
        });
    }

    /** @test */
    public function it_dispatches_challenge_submitted_event_when_work_is_submitted()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'acceptor_id' => $this->acceptor->id,
            'amount' => 100,
            'status' => 'accepted',
        ]);

        $submittedChallenge = $this->challengeService->submitChallenge(
            $challenge,
            $this->acceptor,
            'Work completed!',
            null
        );

        Event::assertDispatched(ChallengeSubmitted::class, function ($event) use ($submittedChallenge) {
            return $event->challenge->id === $submittedChallenge->id
                && $event->submitter->id === $this->acceptor->id;
        });
    }

    /** @test */
    public function it_dispatches_challenge_approved_event_when_work_is_approved()
    {
        // Create and accept challenge first
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => 100, // Type 1: Creator pays acceptor
            'status' => 'open',
        ]);

        $challenge = $this->challengeService->acceptChallenge($challenge, $this->acceptor);

        // Submit work
        $challenge = $this->challengeService->submitChallenge(
            $challenge,
            $this->acceptor,
            'Work completed!',
            null
        );

        // Approve work
        $approvedChallenge = $this->challengeService->approveChallenge($challenge, $this->creator);

        Event::assertDispatched(ChallengeApproved::class, function ($event) use ($approvedChallenge) {
            return $event->challenge->id === $approvedChallenge->id
                && $event->approver->id === $this->creator->id;
        });
    }

    /** @test */
    public function it_dispatches_challenge_rejected_event_when_work_is_rejected()
    {
        // Create and accept challenge first
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => 100,
            'status' => 'open',
        ]);

        $challenge = $this->challengeService->acceptChallenge($challenge, $this->acceptor);

        // Submit work
        $challenge = $this->challengeService->submitChallenge(
            $challenge,
            $this->acceptor,
            'Work completed!',
            null
        );

        // Reject work
        $rejectedChallenge = $this->challengeService->rejectChallenge(
            $challenge,
            $this->creator,
            'Does not meet requirements'
        );

        Event::assertDispatched(ChallengeRejected::class, function ($event) use ($rejectedChallenge) {
            return $event->challenge->id === $rejectedChallenge->id
                && $event->rejector->id === $this->creator->id;
        });
    }

    /** @test */
    public function it_dispatches_challenge_cancelled_event_when_challenge_is_cancelled()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => 100,
            'status' => 'open',
        ]);

        $cancelledChallenge = $this->challengeService->cancelChallenge($challenge, $this->creator);

        Event::assertDispatched(ChallengeCancelled::class, function ($event) use ($cancelledChallenge) {
            return $event->challenge->id === $cancelledChallenge->id
                && $event->cancelledBy->id === $this->creator->id;
        });
    }

    /** @test */
    public function it_dispatches_challenge_expired_event_when_acceptance_deadline_passes()
    {
        // Create challenge with past acceptance deadline
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => 100,
            'status' => 'open',
            'acceptance_deadline' => now()->subHour(),
        ]);

        $count = $this->challengeService->expireOpenChallenges();

        $this->assertEquals(1, $count);

        Event::assertDispatched(ChallengeExpired::class, function ($event) use ($challenge) {
            return $event->challenge->id === $challenge->id;
        });
    }

    /** @test */
    public function it_dispatches_challenge_deadline_missed_event_when_completion_deadline_passes()
    {
        // Create accepted challenge with past completion deadline
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'acceptor_id' => $this->acceptor->id,
            'amount' => 100,
            'status' => 'accepted',
            'accepted_at' => now()->subDays(2),
            'completion_deadline' => now()->subHour(),
        ]);

        $count = $this->challengeService->failPastDeadlineChallenges();

        $this->assertEquals(1, $count);

        Event::assertDispatched(ChallengeDeadlineMissed::class, function ($event) use ($challenge) {
            return $event->challenge->id === $challenge->id;
        });
    }

    /** @test */
    public function it_dispatches_all_events_in_complete_challenge_lifecycle()
    {
        // 1. Create challenge
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => 100,
            'status' => 'open',
        ]);
        \App\Events\ChallengeCreated::dispatch($challenge);

        // 2. Accept challenge
        $challenge = $this->challengeService->acceptChallenge($challenge, $this->acceptor);

        // 3. Submit work
        $challenge = $this->challengeService->submitChallenge(
            $challenge,
            $this->acceptor,
            'Work completed!',
            null
        );

        // 4. Approve work
        $challenge = $this->challengeService->approveChallenge($challenge, $this->creator);

        // Assert all events were dispatched in order
        Event::assertDispatched(ChallengeCreated::class);
        Event::assertDispatched(ChallengeAccepted::class);
        Event::assertDispatched(ChallengeSubmitted::class);
        Event::assertDispatched(ChallengeApproved::class);

        // Verify final state
        $this->assertEquals('completed', $challenge->status);
        $this->assertNotNull($challenge->completed_at);
    }
}
