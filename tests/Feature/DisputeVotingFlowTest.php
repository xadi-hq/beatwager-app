<?php

use App\Enums\DisputeStatus;
use App\Enums\DisputeVoteOutcome;
use App\Events\DisputeResolved;
use App\Events\DisputeVoteReceived;
use App\Models\Dispute;
use App\Models\DisputeVote;
use App\Models\Group;
use App\Models\MessengerService;
use App\Models\User;
use App\Models\Wager;
use App\Services\DisputeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        "api.telegram.org/*" => Http::response(["ok" => true, "result" => true], 200),
    ]);
});

describe("Dispute Voting Eligibility", function () {
    it("allows group member to vote", function () {
        Event::fake([DisputeVoteReceived::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create(["group_id" => $group->id, "status" => "disputed"]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 2,
            "expires_at" => now()->addHours(48),
        ]);

        $disputeService = app(DisputeService::class);
        $vote = $disputeService->castVote($dispute, $voter, DisputeVoteOutcome::OriginalCorrect);

        expect($vote)->toBeInstanceOf(DisputeVote::class);
        expect($vote->voter_id)->toBe($voter->id);
        expect($vote->vote_outcome)->toBe(DisputeVoteOutcome::OriginalCorrect);

        Event::assertDispatched(DisputeVoteReceived::class);
    });

    it("prevents reporter from voting", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "expires_at" => now()->addHours(48),
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->castVote($dispute, $reporter, DisputeVoteOutcome::OriginalCorrect))
            ->toThrow(\InvalidArgumentException::class, "not eligible to vote");
    });

    it("prevents accused from voting", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $group->users()->attach($accused->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "expires_at" => now()->addHours(48),
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->castVote($dispute, $accused, DisputeVoteOutcome::OriginalCorrect))
            ->toThrow(\InvalidArgumentException::class, "not eligible to vote");
    });

    it("prevents non-member from voting", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $nonMember = User::factory()->create();

        // nonMember is NOT attached to group

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "expires_at" => now()->addHours(48),
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->castVote($dispute, $nonMember, DisputeVoteOutcome::OriginalCorrect))
            ->toThrow(\InvalidArgumentException::class, "not eligible to vote");
    });

    it("prevents double voting", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 2,
            "expires_at" => now()->addHours(48),
        ]);

        // First vote
        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => $voter->id,
            "vote_outcome" => DisputeVoteOutcome::OriginalCorrect,
        ]);

        $disputeService = app(DisputeService::class);

        // Second vote attempt
        expect(fn() => $disputeService->castVote($dispute, $voter, DisputeVoteOutcome::DifferentOutcome, "no"))
            ->toThrow(\InvalidArgumentException::class, "not eligible to vote");
    });

    it("prevents voting on expired dispute", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "expires_at" => now()->subHour(), // Expired
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->castVote($dispute, $voter, DisputeVoteOutcome::OriginalCorrect))
            ->toThrow(\InvalidArgumentException::class, "not eligible to vote");
    });

    it("prevents voting on resolved dispute", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Resolved,
            "expires_at" => now()->addHours(48),
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->castVote($dispute, $voter, DisputeVoteOutcome::OriginalCorrect))
            ->toThrow(\InvalidArgumentException::class, "not eligible to vote");
    });
});

describe("Vote Outcome Options", function () {
    it("accepts original correct vote", function () {
        Event::fake([DisputeVoteReceived::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => User::factory()->create()->id,
            "accused_id" => User::factory()->create()->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 2,
            "expires_at" => now()->addHours(48),
        ]);

        $disputeService = app(DisputeService::class);
        $vote = $disputeService->castVote($dispute, $voter, DisputeVoteOutcome::OriginalCorrect);

        expect($vote->vote_outcome)->toBe(DisputeVoteOutcome::OriginalCorrect);
        expect($vote->selected_outcome)->toBeNull();
    });

    it("accepts different outcome vote with selection", function () {
        Event::fake([DisputeVoteReceived::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => User::factory()->create()->id,
            "accused_id" => User::factory()->create()->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 2,
            "expires_at" => now()->addHours(48),
            "original_outcome" => "yes",
        ]);

        $disputeService = app(DisputeService::class);
        $vote = $disputeService->castVote($dispute, $voter, DisputeVoteOutcome::DifferentOutcome, "no");

        expect($vote->vote_outcome)->toBe(DisputeVoteOutcome::DifferentOutcome);
        expect($vote->selected_outcome)->toBe("no");
    });

    it("rejects different outcome vote without selection", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => User::factory()->create()->id,
            "accused_id" => User::factory()->create()->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 2,
            "expires_at" => now()->addHours(48),
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->castVote($dispute, $voter, DisputeVoteOutcome::DifferentOutcome))
            ->toThrow(\InvalidArgumentException::class, "must specify the correct outcome");
    });

    it("accepts not yet determinable vote", function () {
        Event::fake([DisputeVoteReceived::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $voter = User::factory()->create();

        $group->users()->attach($voter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => User::factory()->create()->id,
            "accused_id" => User::factory()->create()->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 2,
            "expires_at" => now()->addHours(48),
        ]);

        $disputeService = app(DisputeService::class);
        $vote = $disputeService->castVote($dispute, $voter, DisputeVoteOutcome::NotYetDeterminable);

        expect($vote->vote_outcome)->toBe(DisputeVoteOutcome::NotYetDeterminable);
    });
});

describe("Vote Threshold Triggers Resolution", function () {
    it("auto resolves when vote threshold is met", function () {
        Event::fake([DisputeResolved::class, DisputeVoteReceived::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter1 = User::factory()->create();
        $voter2 = User::factory()->create();

        $group->users()->attach($voter1->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($voter2->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create(["group_id" => $group->id, "status" => "disputed"]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 2,
            "expires_at" => now()->addHours(48),
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        $disputeService = app(DisputeService::class);

        // First vote - should not resolve
        $disputeService->castVote($dispute, $voter1, DisputeVoteOutcome::OriginalCorrect);
        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Pending);

        // Second vote - should trigger resolution
        $disputeService->castVote($dispute, $voter2, DisputeVoteOutcome::OriginalCorrect);
        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Resolved);

        Event::assertDispatched(DisputeResolved::class);
    });

    it("does not resolve on tie vote", function () {
        Event::fake([DisputeVoteReceived::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();
        $voter1 = User::factory()->create();
        $voter2 = User::factory()->create();

        $group->users()->attach($voter1->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($voter2->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 2,
            "expires_at" => now()->addHours(48),
            "original_outcome" => "yes",
        ]);

        $disputeService = app(DisputeService::class);

        // Split votes - tie
        $disputeService->castVote($dispute, $voter1, DisputeVoteOutcome::OriginalCorrect);
        $disputeService->castVote($dispute, $voter2, DisputeVoteOutcome::DifferentOutcome, "no");

        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Pending); // Still pending, no clear winner
    });
});

describe("Vote Tally", function () {
    it("correctly tallies votes by outcome", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "status" => DisputeStatus::Pending,
        ]);

        // Create votes
        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::OriginalCorrect,
        ]);
        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::OriginalCorrect,
        ]);
        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::DifferentOutcome,
            "selected_outcome" => "no",
        ]);

        $tally = $dispute->getVoteTally();

        expect($tally[DisputeVoteOutcome::OriginalCorrect->value])->toBe(2);
        expect($tally[DisputeVoteOutcome::DifferentOutcome->value])->toBe(1);
        expect($tally[DisputeVoteOutcome::NotYetDeterminable->value])->toBe(0);
    });

    it("determines winning outcome with clear majority", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 2,
        ]);

        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::DifferentOutcome,
            "selected_outcome" => "no",
        ]);
        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::DifferentOutcome,
            "selected_outcome" => "no",
        ]);

        $winningOutcome = $dispute->determineWinningOutcome();

        expect($winningOutcome)->toBe(DisputeVoteOutcome::DifferentOutcome);
    });

    it("gets corrected outcome from votes", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "status" => DisputeStatus::Pending,
            "original_outcome" => "yes",
        ]);

        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::DifferentOutcome,
            "selected_outcome" => "no",
        ]);
        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::DifferentOutcome,
            "selected_outcome" => "no",
        ]);
        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::DifferentOutcome,
            "selected_outcome" => "cancelled", // Different outcome
        ]);

        $correctedOutcome = $dispute->getCorrectedOutcomeFromVotes();

        expect($correctedOutcome)->toBe("no"); // Most common
    });
});
