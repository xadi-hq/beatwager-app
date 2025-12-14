<?php

use App\Enums\DisputeStatus;
use App\Events\DisputeCreated;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\Dispute;
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
    Event::fake([DisputeCreated::class]);
});

describe("Wager Dispute Creation", function () {
    it("creates a dispute for a settled wager", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $settler = User::factory()->create();
        $reporter = User::factory()->create();

        MessengerService::create([
            "user_id" => $settler->id,
            "platform" => "telegram",
            "platform_user_id" => "settler123",
        ]);

        $group->users()->attach($settler->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "creator_id" => $settler->id,
            "status" => "settled",
            "settled_at" => now()->subHour(),
            "outcome_value" => "yes",
            "settler_id" => $settler->id,
        ]);

        $disputeService = app(DisputeService::class);
        $dispute = $disputeService->createDispute($wager, $reporter);

        expect($dispute)->toBeInstanceOf(Dispute::class);
        expect($dispute->status)->toBe(DisputeStatus::Pending);
        expect($dispute->reporter_id)->toBe($reporter->id);
        expect($dispute->accused_id)->toBe($settler->id);
        expect($dispute->is_self_report)->toBeFalse();
        expect($dispute->original_outcome)->toBe("yes");
        expect($dispute->disputable_type)->toBe(Wager::class);
        expect($dispute->disputable_id)->toBe($wager->id);

        // Verify wager state
        $wager->refresh();
        expect($wager->status)->toBe("disputed");
        expect($wager->dispute_id)->toBe($dispute->id);

        Event::assertDispatched(DisputeCreated::class);
    });

    it("creates a self-report dispute", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $settler = User::factory()->create();

        MessengerService::create([
            "user_id" => $settler->id,
            "platform" => "telegram",
            "platform_user_id" => "settler123",
        ]);

        $group->users()->attach($settler->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "creator_id" => $settler->id,
            "status" => "settled",
            "settled_at" => now()->subHour(),
            "settler_id" => $settler->id,
        ]);

        $disputeService = app(DisputeService::class);
        $dispute = $disputeService->createDispute($wager, $settler);

        expect($dispute->is_self_report)->toBeTrue();
        expect($dispute->reporter_id)->toBe($settler->id);
        expect($dispute->accused_id)->toBe($settler->id);
    });

    it("calculates votes required based on group size", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $settler = User::factory()->create();
        $reporter = User::factory()->create();

        MessengerService::create([
            "user_id" => $settler->id,
            "platform" => "telegram",
            "platform_user_id" => "settler123",
        ]);

        // Add 5 members (3 eligible voters after excluding reporter and accused)
        $group->users()->attach($settler->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        for ($i = 0; $i < 3; $i++) {
            $member = User::factory()->create();
            $group->users()->attach($member->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        }

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "settled",
            "settled_at" => now()->subHour(),
            "settler_id" => $settler->id,
        ]);

        $disputeService = app(DisputeService::class);
        $dispute = $disputeService->createDispute($wager, $reporter);

        expect($dispute->votes_required)->toBe(2);
    });

    it("sets 48 hour expiration window", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $settler = User::factory()->create();
        $reporter = User::factory()->create();

        MessengerService::create([
            "user_id" => $settler->id,
            "platform" => "telegram",
            "platform_user_id" => "settler123",
        ]);

        $group->users()->attach($settler->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "settled",
            "settled_at" => now()->subHour(),
            "settler_id" => $settler->id,
        ]);

        $disputeService = app(DisputeService::class);
        $dispute = $disputeService->createDispute($wager, $reporter);

        expect((int) abs(round($dispute->expires_at->diffInHours(now()))))->toBe(48);
    });

    it("rejects dispute for open wager", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "open",
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->createDispute($wager, $reporter))
            ->toThrow(\InvalidArgumentException::class, "This item cannot be disputed.");
    });

    it("rejects dispute for already disputed wager", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $settler = User::factory()->create();
        $reporter = User::factory()->create();

        MessengerService::create([
            "user_id" => $settler->id,
            "platform" => "telegram",
            "platform_user_id" => "settler123",
        ]);

        $existingDispute = Dispute::factory()->create([
            "group_id" => $group->id,
            "status" => DisputeStatus::Pending,
        ]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "disputed",
            "settled_at" => now()->subHour(),
            "settler_id" => $settler->id,
            "dispute_id" => $existingDispute->id,
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->createDispute($wager, $reporter))
            ->toThrow(\InvalidArgumentException::class, "This item cannot be disputed.");
    });

    it("rejects dispute outside 72 hour window", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $settler = User::factory()->create();
        $reporter = User::factory()->create();

        MessengerService::create([
            "user_id" => $settler->id,
            "platform" => "telegram",
            "platform_user_id" => "settler123",
        ]);

        $wager = Wager::factory()->create([
            "group_id" => $group->id,
            "status" => "settled",
            "settled_at" => now()->subHours(73), // Outside 72h window
            "settler_id" => $settler->id,
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->createDispute($wager, $reporter))
            ->toThrow(\InvalidArgumentException::class, "This item cannot be disputed.");
    });
});

describe("Elimination Challenge Dispute Creation", function () {
    it("creates dispute for elimination challenge survivor", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $accusedSurvivor = User::factory()->create();

        $group->users()->attach($reporter->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);
        $group->users()->attach($accusedSurvivor->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000]);

        // Create elimination challenge with proper status (open = active)
        $challenge = Challenge::factory()->create([
            "group_id" => $group->id,
            "type" => \App\Enums\ChallengeType::ELIMINATION_CHALLENGE,
            "status" => "open",
        ]);

        // Add survivor as participant (active = no eliminated_at)
        ChallengeParticipant::create([
            "challenge_id" => $challenge->id,
            "user_id" => $accusedSurvivor->id,
            "accepted_at" => now()->subDay(),
            "eliminated_at" => null,
        ]);

        $disputeService = app(DisputeService::class);
        $dispute = $disputeService->createEliminationDispute($challenge, $reporter, $accusedSurvivor);

        expect($dispute->disputable_type)->toBe(Challenge::class);
        expect($dispute->disputable_id)->toBe($challenge->id);
        expect($dispute->accused_id)->toBe($accusedSurvivor->id);
        expect($dispute->original_outcome)->toBe("survivor");
        expect($dispute->is_self_report)->toBeFalse();
    });

    it("rejects dispute for eliminated participant", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $eliminatedUser = User::factory()->create();

        $challenge = Challenge::factory()->create([
            "group_id" => $group->id,
            "type" => \App\Enums\ChallengeType::ELIMINATION_CHALLENGE,
            "status" => "open",
        ]);

        // Add as eliminated participant (has eliminated_at set)
        ChallengeParticipant::create([
            "challenge_id" => $challenge->id,
            "user_id" => $eliminatedUser->id,
            "accepted_at" => now()->subDay(),
            "eliminated_at" => now()->subHour(),
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->createEliminationDispute($challenge, $reporter, $eliminatedUser))
            ->toThrow(\InvalidArgumentException::class, "not an active participant");
    });

    it("rejects dispute for non-elimination challenge", function () {
        $group = Group::factory()->create(["platform" => "telegram"]);
        $reporter = User::factory()->create();
        $user = User::factory()->create();

        $challenge = Challenge::factory()->create([
            "group_id" => $group->id,
            "type" => \App\Enums\ChallengeType::USER_CHALLENGE,
        ]);

        $disputeService = app(DisputeService::class);

        expect(fn() => $disputeService->createEliminationDispute($challenge, $reporter, $user))
            ->toThrow(\InvalidArgumentException::class, "not an elimination challenge");
    });
});
