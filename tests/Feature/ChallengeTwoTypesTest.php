<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeTwoTypesTest extends TestCase
{
    use RefreshDatabase;

    private User $creator;
    private User $acceptor;
    private Group $group;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->creator = User::factory()->create();
        $this->acceptor = User::factory()->create();

        // Create group
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
    }

    /** @test */
    public function it_creates_type_1_challenge_with_positive_amount()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => 200, // Positive = Type 1 (offering payment)
        ]);

        $this->assertTrue($challenge->isOfferingPayment());
        $this->assertFalse($challenge->isOfferingService());
        $this->assertEquals(200, $challenge->getAbsoluteAmount());
        $this->assertEquals($this->creator->id, $challenge->getPayer()->id);
    }

    /** @test */
    public function it_creates_type_2_challenge_with_negative_amount()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => -200, // Negative = Type 2 (offering service)
        ]);

        $this->assertFalse($challenge->isOfferingPayment());
        $this->assertTrue($challenge->isOfferingService());
        $this->assertEquals(200, $challenge->getAbsoluteAmount());
    }

    /** @test */
    public function type_1_challenge_reserves_points_from_creator()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'acceptor_id' => null,
            'amount' => 200,
            'status' => 'open',
        ]);

        // Accept challenge - should reserve from creator
        app(\App\Services\ChallengeService::class)->acceptChallenge($challenge, $this->acceptor);

        $challenge->refresh();

        // Creator's balance should be reduced
        $creatorBalance = $this->group->users()
            ->where('user_id', $this->creator->id)
            ->first()
            ->pivot
            ->points;

        $this->assertEquals(800, $creatorBalance); // 1000 - 200
        $this->assertEquals('accepted', $challenge->status);
    }

    /** @test */
    public function type_2_challenge_reserves_points_from_acceptor()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'acceptor_id' => null,
            'amount' => -200, // Negative = Type 2
            'status' => 'open',
        ]);

        // Accept challenge - should reserve from acceptor (not creator)
        app(\App\Services\ChallengeService::class)->acceptChallenge($challenge, $this->acceptor);

        $challenge->refresh();

        // Acceptor's balance should be reduced (they are paying)
        $acceptorBalance = $this->group->users()
            ->where('user_id', $this->acceptor->id)
            ->first()
            ->pivot
            ->points;

        // Creator's balance should stay the same
        $creatorBalance = $this->group->users()
            ->where('user_id', $this->creator->id)
            ->first()
            ->pivot
            ->points;

        $this->assertEquals(800, $acceptorBalance); // 1000 - 200 (acceptor pays)
        $this->assertEquals(1000, $creatorBalance); // Creator unchanged
        $this->assertEquals('accepted', $challenge->status);
    }

    /** @test */
    public function type_1_challenge_pays_acceptor_on_completion()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => 200,
            'status' => 'accepted',
            'acceptor_id' => $this->acceptor->id,
            'accepted_at' => now(),
            'submitted_at' => now(),
        ]);

        // Create hold transaction
        $holdTransaction = \App\Models\Transaction::create([
            'user_id' => $this->creator->id,
            'group_id' => $this->group->id,
            'type' => 'challenge_hold',
            'amount' => -200,
            'balance_before' => 1000,
            'balance_after' => 800,
            'challenge_id' => $challenge->id,
            'description' => 'Challenge hold',
        ]);

        $challenge->update(['hold_transaction_id' => $holdTransaction->id]);

        // Update creator balance to reflect hold
        $this->group->users()->updateExistingPivot($this->creator->id, ['points' => 800]);

        // Approve challenge
        app(\App\Services\ChallengeService::class)->approveChallenge($challenge, $this->creator);

        $challenge->refresh();

        // Acceptor should receive points
        $acceptorBalance = $this->group->users()
            ->where('user_id', $this->acceptor->id)
            ->first()
            ->pivot
            ->points;

        $this->assertEquals(1200, $acceptorBalance); // 1000 + 200
        $this->assertEquals('completed', $challenge->status);
    }

    /** @test */
    public function type_2_challenge_pays_creator_on_completion()
    {
        $challenge = Challenge::factory()->create([
            'group_id' => $this->group->id,
            'creator_id' => $this->creator->id,
            'amount' => -200, // Type 2
            'status' => 'accepted',
            'acceptor_id' => $this->acceptor->id,
            'accepted_at' => now(),
            'submitted_at' => now(),
        ]);

        // Create hold transaction (from acceptor)
        $holdTransaction = \App\Models\Transaction::create([
            'user_id' => $this->acceptor->id,
            'group_id' => $this->group->id,
            'type' => 'challenge_hold',
            'amount' => -200,
            'balance_before' => 1000,
            'balance_after' => 800,
            'challenge_id' => $challenge->id,
            'description' => 'Challenge hold',
        ]);

        $challenge->update(['hold_transaction_id' => $holdTransaction->id]);

        // Update acceptor balance to reflect hold
        $this->group->users()->updateExistingPivot($this->acceptor->id, ['points' => 800]);

        // Approve challenge - creator should receive points
        app(\App\Services\ChallengeService::class)->approveChallenge($challenge, $this->acceptor);

        $challenge->refresh();

        // Creator should receive points (they did the work)
        $creatorBalance = $this->group->users()
            ->where('user_id', $this->creator->id)
            ->first()
            ->pivot
            ->points;

        $this->assertEquals(1200, $creatorBalance); // 1000 + 200
        $this->assertEquals('completed', $challenge->status);
    }
}
