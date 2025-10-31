<?php

namespace Tests\Unit\Listeners;

use App\DTOs\Message;
use App\Events\ChallengeAccepted;
use App\Events\ChallengeApproved;
use App\Events\ChallengeCancelled;
use App\Events\ChallengeDeadlineMissed;
use App\Events\ChallengeExpired;
use App\Events\ChallengeRejected;
use App\Events\ChallengeSubmitted;
use App\Listeners\SendChallengeAcceptedAnnouncement;
use App\Listeners\SendChallengeApprovedAnnouncement;
use App\Listeners\SendChallengeCancelledAnnouncement;
use App\Listeners\SendChallengeDeadlineMissedAnnouncement;
use App\Listeners\SendChallengeExpiredAnnouncement;
use App\Listeners\SendChallengeRejectedAnnouncement;
use App\Listeners\SendChallengeSubmittedAnnouncement;
use App\Models\Challenge;
use App\Models\Group;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class ChallengeEventListenersTest extends TestCase
{
    use RefreshDatabase;

    private $mockGroup;
    private Group $realGroup;
    private User $creator;
    private User $acceptor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = User::factory()->create();
        $this->acceptor = User::factory()->create();
        $this->realGroup = Group::factory()->create();
        $this->mockGroup = Mockery::mock(Group::class);
    }

    /** @test */
    public function challenge_accepted_listener_sends_announcement()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->realGroup->id,
            'creator_id' => $this->creator->id,
            'acceptor_id' => $this->acceptor->id,
        ]);

        $challenge->setRelation('group', $this->mockGroup);

        $messageService = Mockery::mock(MessageService::class);
        $message = Mockery::mock(Message::class);

        $messageService->shouldReceive('challengeAccepted')
            ->once()
            ->with(
                Mockery::on(fn($c) => $c->id === $challenge->id),
                Mockery::on(fn($u) => $u->id === $this->acceptor->id)
            )
            ->andReturn($message);

        $this->mockGroup->shouldReceive('sendMessage')
            ->once()
            ->with($message);

        $listener = new SendChallengeAcceptedAnnouncement($messageService);
        $event = new ChallengeAccepted($challenge, $this->acceptor);

        $listener->handle($event);
    }

    /** @test */
    public function challenge_submitted_listener_sends_announcement()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->realGroup->id,
            'creator_id' => $this->creator->id,
            'acceptor_id' => $this->acceptor->id,
            'status' => 'accepted',
        ]);

        $challenge->setRelation('group', $this->mockGroup);

        $messageService = Mockery::mock(MessageService::class);
        $message = Mockery::mock(Message::class);

        $messageService->shouldReceive('challengeSubmitted')
            ->once()
            ->with(
                Mockery::on(fn($c) => $c->id === $challenge->id),
                Mockery::on(fn($u) => $u->id === $this->acceptor->id)
            )
            ->andReturn($message);

        $this->mockGroup->shouldReceive('sendMessage')
            ->once()
            ->with($message);

        $listener = new SendChallengeSubmittedAnnouncement($messageService);
        $event = new ChallengeSubmitted($challenge, $this->acceptor);

        $listener->handle($event);
    }

    /** @test */
    public function challenge_approved_listener_sends_announcement()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->realGroup->id,
            'creator_id' => $this->creator->id,
            'acceptor_id' => $this->acceptor->id,
            'status' => 'completed',
        ]);

        $challenge->setRelation('group', $this->mockGroup);

        $messageService = Mockery::mock(MessageService::class);
        $message = Mockery::mock(Message::class);

        $messageService->shouldReceive('challengeApproved')
            ->once()
            ->with(
                Mockery::on(fn($c) => $c->id === $challenge->id),
                Mockery::on(fn($u) => $u->id === $this->creator->id)
            )
            ->andReturn($message);

        $this->mockGroup->shouldReceive('sendMessage')
            ->once()
            ->with($message);

        $listener = new SendChallengeApprovedAnnouncement($messageService);
        $event = new ChallengeApproved($challenge, $this->creator);

        $listener->handle($event);
    }

    /** @test */
    public function challenge_rejected_listener_sends_announcement()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->realGroup->id,
            'creator_id' => $this->creator->id,
            'acceptor_id' => $this->acceptor->id,
            'status' => 'failed',
        ]);

        $challenge->setRelation('group', $this->mockGroup);

        $messageService = Mockery::mock(MessageService::class);
        $message = Mockery::mock(Message::class);

        $messageService->shouldReceive('challengeRejected')
            ->once()
            ->with(
                Mockery::on(fn($c) => $c->id === $challenge->id),
                Mockery::on(fn($u) => $u->id === $this->creator->id)
            )
            ->andReturn($message);

        $this->mockGroup->shouldReceive('sendMessage')
            ->once()
            ->with($message);

        $listener = new SendChallengeRejectedAnnouncement($messageService);
        $event = new ChallengeRejected($challenge, $this->creator);

        $listener->handle($event);
    }

    /** @test */
    public function challenge_cancelled_listener_sends_announcement()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->realGroup->id,
            'creator_id' => $this->creator->id,
            'status' => 'cancelled',
        ]);

        $challenge->setRelation('group', $this->mockGroup);

        $messageService = Mockery::mock(MessageService::class);
        $message = Mockery::mock(Message::class);

        $messageService->shouldReceive('challengeCancelled')
            ->once()
            ->with(
                Mockery::on(fn($c) => $c->id === $challenge->id),
                Mockery::on(fn($u) => $u->id === $this->creator->id)
            )
            ->andReturn($message);

        $this->mockGroup->shouldReceive('sendMessage')
            ->once()
            ->with($message);

        $listener = new SendChallengeCancelledAnnouncement($messageService);
        $event = new ChallengeCancelled($challenge, $this->creator);

        $listener->handle($event);
    }

    /** @test */
    public function challenge_expired_listener_sends_announcement()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->realGroup->id,
            'creator_id' => $this->creator->id,
            'status' => 'cancelled',
            'failure_reason' => 'Acceptance deadline passed',
        ]);

        $challenge->setRelation('group', $this->mockGroup);

        $messageService = Mockery::mock(MessageService::class);
        $message = Mockery::mock(Message::class);

        $messageService->shouldReceive('challengeExpired')
            ->once()
            ->with(Mockery::on(fn($c) => $c->id === $challenge->id))
            ->andReturn($message);

        $this->mockGroup->shouldReceive('sendMessage')
            ->once()
            ->with($message);

        $listener = new SendChallengeExpiredAnnouncement($messageService);
        $event = new ChallengeExpired($challenge);

        $listener->handle($event);
    }

    /** @test */
    public function challenge_deadline_missed_listener_sends_announcement()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->realGroup->id,
            'creator_id' => $this->creator->id,
            'acceptor_id' => $this->acceptor->id,
            'status' => 'failed',
            'failure_reason' => 'Completion deadline passed',
        ]);

        $challenge->setRelation('group', $this->mockGroup);

        $messageService = Mockery::mock(MessageService::class);
        $message = Mockery::mock(Message::class);

        $messageService->shouldReceive('challengeDeadlineMissed')
            ->once()
            ->with(Mockery::on(fn($c) => $c->id === $challenge->id))
            ->andReturn($message);

        $this->mockGroup->shouldReceive('sendMessage')
            ->once()
            ->with($message);

        $listener = new SendChallengeDeadlineMissedAnnouncement($messageService);
        $event = new ChallengeDeadlineMissed($challenge);

        $listener->handle($event);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
