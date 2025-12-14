<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DisputeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireDisputes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'disputes:expire';

    /**
     * The console command description.
     */
    protected $description = 'Process expired disputes (48h voting window passed)';

    /**
     * Execute the console command.
     */
    public function handle(DisputeService $disputeService): int
    {
        $expiredDisputes = $disputeService->getExpiredDisputes();

        if ($expiredDisputes->isEmpty()) {
            $this->info('No expired disputes to process.');
            return self::SUCCESS;
        }

        $this->info("Found {$expiredDisputes->count()} expired dispute(s).");

        $processed = 0;
        $failed = 0;

        foreach ($expiredDisputes as $dispute) {
            try {
                $disputeService->handleExpiredDispute($dispute);
                $processed++;

                $this->info("Processed expired dispute {$dispute->id} - resolved as: {$dispute->fresh()->resolution?->value}");

            } catch (\Exception $e) {
                $failed++;
                $this->error("Failed to process dispute {$dispute->id}: {$e->getMessage()}");
                Log::error('Failed to process expired dispute', [
                    'dispute_id' => $dispute->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("Processed {$processed} dispute(s), {$failed} failed.");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
