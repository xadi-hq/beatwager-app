<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\SuperChallengeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Process SuperChallenge auto-approvals (48h timeout)
 *
 * Runs hourly to check for completion claims that have been pending for 48+ hours
 * and automatically approve them if the creator hasn't responded.
 */
class ProcessSuperChallengeAutoApprovals implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(SuperChallengeService $service): void
    {
        Log::info('ProcessSuperChallengeAutoApprovals: Starting job');

        try {
            $service->processAutoApprovals();

            Log::info('ProcessSuperChallengeAutoApprovals: Job completed successfully');
        } catch (\Exception $e) {
            Log::error('ProcessSuperChallengeAutoApprovals: Job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry logic
        }
    }
}
