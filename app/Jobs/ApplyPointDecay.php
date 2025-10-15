<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Group;
use App\Services\PointService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ApplyPointDecay implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     * Process all groups and apply point decay to inactive users
     */
    public function handle(PointService $pointService): void
    {
        Log::info('ApplyPointDecay job started');

        $totalResults = [
            'groups_processed' => 0,
            'warnings_sent' => 0,
            'decay_applied' => 0,
            'total_users_processed' => 0,
        ];

        // Process all groups in chunks
        Group::chunk(50, function ($groups) use ($pointService, &$totalResults) {
            foreach ($groups as $group) {
                try {
                    $results = $pointService->applyDecayForGroup($group);

                    $totalResults['groups_processed']++;
                    $totalResults['warnings_sent'] += $results['warnings_sent'];
                    $totalResults['decay_applied'] += $results['decay_applied'];
                    $totalResults['total_users_processed'] += $results['total_processed'];

                    Log::info("Processed group {$group->id}: {$results['decay_applied']} decay applied, {$results['warnings_sent']} warnings sent");
                } catch (\Exception $e) {
                    Log::error("Failed to process group {$group->id}: " . $e->getMessage());
                }
            }
        });

        Log::info('ApplyPointDecay job completed', $totalResults);
    }
}
