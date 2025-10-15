<?php

use App\Models\Group;
use App\Models\User;
use App\Models\Wager;
use App\Services\WagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake([
        "api.telegram.org/*" => Http::response(["ok" => true, "result" => true], 200),
    ]);
});

describe("Binary Wager Settlement", function () {
    it("settles binary wager with winners", function () {
        $creator = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $group = Group::factory()->create();

        $group->users()->attach($creator->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000, "role" => "participant"]);
        $group->users()->attach($user1->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000, "role" => "participant"]);
        $group->users()->attach($user2->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000, "role" => "participant"]);
        $group->users()->attach($user3->id, ["id" => \Illuminate\Support\Str::uuid(), "points" => 1000, "role" => "participant"]);

        $wager = Wager::create([
            "creator_id" => $creator->id,
            "group_id" => $group->id,
            "title" => "Will it rain?",
            "type" => "binary",
            "stake_amount" => 100,
            "deadline" => now()->addHours(24),
            "status" => "open",
            "total_points_wagered" => 0,
            "participants_count" => 0,
        ]);

        $wagerService = app(WagerService::class);
        $wagerService->placeWager($wager, $user1, "yes", 100);
        $wagerService->placeWager($wager, $user2, "yes", 100);
        $wagerService->placeWager($wager, $user3, "no", 100);

        $wagerService->settleWager($wager, "yes", "It did rain");

        $wager->refresh();
        expect($wager->status)->toBe("settled");
        expect($wager->outcome_value)->toBe("yes");

        $winners = $wager->entries()->where("is_winner", true)->get();
        expect($winners)->toHaveCount(2);

        foreach ($winners as $winner) {
            expect($winner->points_won)->toBe(150);
        }

        $user1Balance = $user1->groups()->where("group_id", $group->id)->first()->pivot->points;
        $user2Balance = $user2->groups()->where("group_id", $group->id)->first()->pivot->points;
        $user3Balance = $user3->groups()->where("group_id", $group->id)->first()->pivot->points;

        expect($user1Balance)->toBe(1050);
        expect($user2Balance)->toBe(1050);
        expect($user3Balance)->toBe(900);
    });
});
