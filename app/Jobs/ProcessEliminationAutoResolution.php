<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ChallengeType;
use App\Enums\EliminationMode;
use App\Models\Challenge;
use App\Services\EliminationChallengeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Automatically resolve elimination challenges when:
 * - Deadline mode: completion_deadline has passed
 * - Last man standing: only one survivor remains (handled by service on tap-out)
 * - Insufficient participants at tap-in deadline
 */
class ProcessEliminationAutoResolution implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(EliminationChallengeService $eliminationService): void
    {
        Log::info('ProcessEliminationAutoResolution job started');

        $resolved = 0;
        $cancelled = 0;

        // 1. Resolve deadline-mode challenges that have reached their deadline
        $deadlineReached = Challenge::query()
            ->where('type', ChallengeType::ELIMINATION_CHALLENGE->value)
            ->where('status', 'open')
            ->where('elimination_mode', EliminationMode::DEADLINE->value)
            ->whereNotNull('completion_deadline')
            ->where('completion_deadline', '<=', now())
            ->get();

        foreach ($deadlineReached as $challenge) {
            try {
                $eliminationService->resolve($challenge);
                Log::info("Auto-resolved deadline elimination challenge", [
                    'challenge_id' => $challenge->id,
                    'survivor_count' => $challenge->getSurvivors()->count(),
                ]);
                $resolved++;
            } catch (\Exception $e) {
                Log::error("Failed to auto-resolve elimination challenge", [
                    'challenge_id' => $challenge->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 2. Cancel challenges that didn't reach minimum participants by tap-in deadline
        $needsCancellation = Challenge::query()
            ->where('type', ChallengeType::ELIMINATION_CHALLENGE->value)
            ->where('status', 'open')
            ->whereNotNull('tap_in_deadline')
            ->where('tap_in_deadline', '<=', now())
            ->get()
            ->filter(fn(Challenge $c) => !$c->hasMinimumParticipants());

        foreach ($needsCancellation as $challenge) {
            try {
                $eliminationService->cancel($challenge, null);
                Log::info("Auto-cancelled elimination challenge due to insufficient participants", [
                    'challenge_id' => $challenge->id,
                    'participant_count' => $challenge->participants()->count(),
                    'min_required' => $challenge->min_participants,
                ]);
                $cancelled++;
            } catch (\Exception $e) {
                Log::error("Failed to auto-cancel elimination challenge", [
                    'challenge_id' => $challenge->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("ProcessEliminationAutoResolution job completed", [
            'resolved' => $resolved,
            'cancelled' => $cancelled,
        ]);
    }
}
