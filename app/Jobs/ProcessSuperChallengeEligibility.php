<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\SuperChallengeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Process groups eligible for SuperChallenge creation
 *
 * Runs daily to check which groups are due for a new SuperChallenge based on:
 * - Frequency setting (weekly/monthly/quarterly)
 * - Last SuperChallenge date
 * - Active group members
 */
class ProcessSuperChallengeEligibility implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(SuperChallengeService $service): void
    {
        Log::info('ProcessSuperChallengeEligibility: Starting job');

        try {
            $service->processEligibleGroups();

            Log::info('ProcessSuperChallengeEligibility: Job completed successfully');
        } catch (\Exception $e) {
            Log::error('ProcessSuperChallengeEligibility: Job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry logic
        }
    }
}
