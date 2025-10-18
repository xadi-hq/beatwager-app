<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Services\MessageService;
use App\Services\SeasonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckSeasonEndings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seasons:check
                            {--dry-run : Show what would be done without ending seasons}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for seasons that should end and process them';

    /**
     * Execute the console command.
     */
    public function handle(SeasonService $seasonService, MessageService $messageService): int
    {
        $this->info('Checking for seasons that should end...');

        // Find groups with seasons that should end
        $groupsToEnd = Group::whereNotNull('season_ends_at')
            ->whereNotNull('current_season_id')
            ->where('season_ends_at', '<=', now())
            ->with('currentSeason')
            ->get();

        if ($groupsToEnd->isEmpty()) {
            $this->info('No seasons need to be ended.');
            return self::SUCCESS;
        }

        $this->info("Found {$groupsToEnd->count()} season(s) to end");

        foreach ($groupsToEnd as $group) {
            $season = $group->currentSeason;

            $this->line("Group: {$group->name} (Season {$season->season_number})");

            if ($this->option('dry-run')) {
                $this->comment('  [DRY RUN] Would end season and send recap');
                continue;
            }

            try {
                // End the season (calculates standings and archives)
                $endedSeason = $seasonService->endSeason($group);

                // Send season ending announcement with recap
                $message = $messageService->seasonEnded($group, $endedSeason);
                $group->sendMessage($message);

                $this->info('  ✓ Season ended and recap sent');

                Log::channel('operational')->info('season.ended_automatically', [
                    'group_id' => $group->id,
                    'season_id' => $endedSeason->id,
                    'season_number' => $endedSeason->season_number,
                    'duration_days' => $endedSeason->getDurationInDays(),
                ]);
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed to end season: {$e->getMessage()}");

                Log::channel('operational')->error('season.end_failed', [
                    'group_id' => $group->id,
                    'season_id' => $season->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}
