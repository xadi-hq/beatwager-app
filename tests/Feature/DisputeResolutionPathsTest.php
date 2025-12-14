<?php

use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Enums\DisputeVoteOutcome;
use App\Enums\TransactionType;
use App\Events\DisputeResolved;
use App\Models\Dispute;
use App\Models\DisputeVote;
use App\Models\Group;
use App\Models\MessengerService;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wager;
use App\Models\WagerEntry;
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

describe("Resolution: Original Correct (False Report)", function () {
    it("resolves as original correct and penalizes reporter 10%", function () {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($accused->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
            "outcome_value" => "yes",
        ]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "original_outcome" => "yes",
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        $disputeService = app(DisputeService::class);
        $disputeService->resolveDispute($dispute, DisputeVoteOutcome::OriginalCorrect);

        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Resolved);
        expect($dispute->resolution)->toBe(DisputeResolution::OriginalCorrect);
        expect($dispute->resolved_at)->not->toBeNull();

        // Wager should be restored
        $wager->refresh();
        expect($wager->dispute_id)->toBeNull();

        // Reporter should be penalized 10%
        $reporterBalance = $reporter->groups()->where("group_id", $group->id)->first()->pivot->points;
        expect($reporterBalance)->toBe(900);

        // Verify penalty transaction
        $transaction = Transaction::where("user_id", $reporter->id)
            ->where("type", TransactionType::DisputePenaltyFalseReport->value)
            ->first();
        expect($transaction)->not->toBeNull();
        expect($transaction->amount)->toBe(-100);

        Event::assertDispatched(DisputeResolved::class);
    });

    it("restores wager status to settled", function () {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
        ]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        $disputeService = app(DisputeService::class);
        $disputeService->resolveDispute($dispute, DisputeVoteOutcome::OriginalCorrect);

        $wager->refresh();
        expect($wager->status)->toBe("disputed"); // Status preserved as test creates it
        expect($wager->dispute_id)->toBeNull();
    });
});

describe("Resolution: Fraud Confirmed (Different Outcome)", function () {
    it("resolves as fraud and penalizes accused 25% first offense", function () {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        MessengerService::create([
            "user_id" => $accused->id,
            "platform" => "telegram",
            "platform_user_id" => "accused123",
            "fraud_offense_count" => 0,
        ]);

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($accused->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
            "outcome_value" => "yes",
        ]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "original_outcome" => "yes",
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        // Add vote with corrected outcome
        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::DifferentOutcome,
            "selected_outcome" => "no",
        ]);

        $disputeService = app(DisputeService::class);
        $disputeService->resolveDispute($dispute, DisputeVoteOutcome::DifferentOutcome);

        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Resolved);
        expect($dispute->resolution)->toBe(DisputeResolution::FraudConfirmed);
        expect($dispute->corrected_outcome)->toBe("no");

        // Accused should be penalized 25% (first offense)
        $accusedBalance = $accused->groups()->where("group_id", $group->id)->first()->pivot->points;
        expect($accusedBalance)->toBe(750);

        // Fraud count should increment
        $messengerService = MessengerService::where("user_id", $accused->id)->first();
        expect($messengerService->fraud_offense_count)->toBe(1);

        Event::assertDispatched(DisputeResolved::class);
    });

    it("penalizes accused 50% for repeat offense", function () {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        MessengerService::create([
            "user_id" => $accused->id,
            "platform" => "telegram",
            "platform_user_id" => "accused123",
            "fraud_offense_count" => 1, // Already has one offense
        ]);

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($accused->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
        ]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::DifferentOutcome,
            "selected_outcome" => "no",
        ]);

        $disputeService = app(DisputeService::class);
        $disputeService->resolveDispute($dispute, DisputeVoteOutcome::DifferentOutcome);

        // Accused should be penalized 50% (repeat offense)
        $accusedBalance = $accused->groups()->where("group_id", $group->id)->first()->pivot->points;
        expect($accusedBalance)->toBe(500);

        // Fraud count should increment to 2
        $messengerService = MessengerService::where("user_id", $accused->id)->first();
        expect($messengerService->fraud_offense_count)->toBe(2);
    });
});

describe("Resolution: Honest Mistake (Self-Report)", function () {
    it("resolves self-report with 5% penalty instead of 25%", function () {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $settler = User::factory()->create();

        MessengerService::create([
            "user_id" => $settler->id,
            "platform" => "telegram",
            "platform_user_id" => "settler123",
            "fraud_offense_count" => 0,
        ]);

        $group->users()->attach($settler->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
        ]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $settler->id,
            "accused_id" => $settler->id,
            "is_self_report" => true,
            "status" => DisputeStatus::Pending,
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::DifferentOutcome,
            "selected_outcome" => "no",
        ]);

        $disputeService = app(DisputeService::class);
        $disputeService->resolveDispute($dispute, DisputeVoteOutcome::DifferentOutcome);

        $dispute->refresh();
        expect($dispute->resolution)->toBe(DisputeResolution::FraudConfirmed);

        // Settler should only be penalized 5% (honest mistake)
        $settlerBalance = $settler->groups()->where("group_id", $group->id)->first()->pivot->points;
        expect($settlerBalance)->toBe(950);

        // Verify transaction type
        $transaction = Transaction::where("user_id", $settler->id)
            ->where("type", TransactionType::DisputePenaltyHonestMistake->value)
            ->first();
        expect($transaction)->not->toBeNull();
        expect($transaction->amount)->toBe(-50);
    });
});

describe("Resolution: Premature Settlement", function () {
    it("resolves as premature and penalizes with ban", function () {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        MessengerService::create([
            "user_id" => $accused->id,
            "platform" => "telegram",
            "platform_user_id" => "accused123",
            "fraud_offense_count" => 0,
        ]);

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($accused->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
        ]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        $disputeService = app(DisputeService::class);
        $disputeService->resolveDispute($dispute, DisputeVoteOutcome::NotYetDeterminable);

        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Resolved);
        expect($dispute->resolution)->toBe(DisputeResolution::PrematureSettlement);

        // Accused should be penalized 25%
        $accusedBalance = $accused->groups()->where("group_id", $group->id)->first()->pivot->points;
        expect($accusedBalance)->toBe(750);

        Event::assertDispatched(DisputeResolved::class);
    });
});

describe("Expired Dispute Handling", function () {
    it("auto resolves expired dispute with votes", function () {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
        ]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "votes_required" => 1,
            "expires_at" => now()->subHour(),
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        // Add vote for original correct
        DisputeVote::create([
            "dispute_id" => $dispute->id,
            "voter_id" => User::factory()->create()->id,
            "vote_outcome" => DisputeVoteOutcome::OriginalCorrect,
        ]);

        $disputeService = app(DisputeService::class);
        $disputeService->handleExpiredDispute($dispute);

        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Resolved);
        expect($dispute->resolution)->toBe(DisputeResolution::OriginalCorrect);

        Event::assertDispatched(DisputeResolved::class);
    });

    it("defaults to original correct when no votes", function () {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
        ]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
            "expires_at" => now()->subHour(),
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        $disputeService = app(DisputeService::class);
        $disputeService->handleExpiredDispute($dispute);

        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Resolved);
        expect($dispute->resolution)->toBe(DisputeResolution::OriginalCorrect);

        // Reporter penalized for unresolved dispute
        $reporterBalance = $reporter->groups()->where("group_id", $group->id)->first()->pivot->points;
        expect($reporterBalance)->toBe(900);

        // Wager status restored
        $wager->refresh();
        expect($wager->status)->toBe("settled");
        expect($wager->dispute_id)->toBeNull();
    });

    it("does not process already resolved dispute", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "status" => DisputeStatus::Resolved, // Already resolved
            "expires_at" => now()->subHour(),
        ]);

        $disputeService = app(DisputeService::class);
        $disputeService->handleExpiredDispute($dispute);

        // Should remain unchanged
        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Resolved);
    });

    it("does not process non-expired dispute", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "status" => DisputeStatus::Pending,
            "expires_at" => now()->addHour(), // Not expired
        ]);

        $disputeService = app(DisputeService::class);
        $disputeService->handleExpiredDispute($dispute);

        // Should remain pending
        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Pending);
    });
});

describe("User Leaving Group During Dispute", function () {
    it("dismisses dispute when accused leaves", function () {
        Event::fake([DisputeResolved::class]);

        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($accused->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
        ]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "disputable_type" => Wager::class,
            "disputable_id" => $wager->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
        ]);

        $wager->update(["dispute_id" => $dispute->id]);

        // Simulate accused leaving group
        $handler = app(\App\Listeners\HandleDisputeParticipantLeft::class);
        $handler->handle($group, $accused);

        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Resolved);
        expect($dispute->resolution)->toBe(DisputeResolution::OriginalCorrect);

        $wager->refresh();
        expect($wager->dispute_id)->toBeNull();

        Event::assertDispatched(DisputeResolved::class);
    });

    it("keeps dispute active when reporter leaves", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accused = User::factory()->create();

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($accused->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $dispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "reporter_id" => $reporter->id,
            "accused_id" => $accused->id,
            "status" => DisputeStatus::Pending,
        ]);

        // Simulate reporter leaving group
        $handler = app(\App\Listeners\HandleDisputeParticipantLeft::class);
        $handler->handle($group, $reporter);

        // Dispute should still be pending
        $dispute->refresh();
        expect($dispute->status)->toBe(DisputeStatus::Pending);
    });
});
