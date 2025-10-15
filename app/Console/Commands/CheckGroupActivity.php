<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Services\MessageService;
use App\Services\MessageTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckGroupActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:check
                            {--dry-run : Show what would be done without sending messages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for inactive groups and send revival messages';

    /**
     * Execute the console command.
     */
    public function handle(MessageService $messageService, MessageTrackingService $trackingService): int
    {
        // Feature flag check - exit early if feature disabled
        if (!config('features.activity_tracking', false)) {
            $this->info('Activity tracking feature is disabled. Enable with FEATURE_ACTIVITY_TRACKING=true');
            return self::SUCCESS;
        }

        $this->info('Checking for inactive groups...');

        // Find groups that are:
        // 1. Active
        // 2. Have last_activity_at set
        // 3. Haven't had activity in X days (based on their threshold)
        $inactiveGroups = Group::where('is_active', true)
            ->whereNotNull('last_activity_at')
            ->whereRaw('last_activity_at < NOW() - INTERVAL \'1 day\' * inactivity_threshold_days')
            ->get();

        if ($inactiveGroups->isEmpty()) {
            $this->info('No inactive groups found.');
            return self::SUCCESS;
        }

        $this->info("Found {$inactiveGroups->count()} inactive group(s)");

        foreach ($inactiveGroups as $group) {
            $daysInactive = now()->diffInDays($group->last_activity_at);

            $this->line("Group: {$group->name} (inactive for {$daysInactive} days)");

            if ($this->option('dry-run')) {
                $this->comment('  [DRY RUN] Would send revival message');
                continue;
            }

            try {
                // Generate and send revival message
                $message = $messageService->revivalMessage($group, $daysInactive);
                $group->sendMessage($message);

                $this->info('  ✓ Sent revival message');

                Log::channel('operational')->info('activity_tracking.revival_sent', [
                    'group_id' => $group->id,
                    'days_inactive' => $daysInactive,
                    'threshold' => $group->inactivity_threshold_days,
                ]);
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed to send revival message: {$e->getMessage()}");

                Log::channel('operational')->error('activity_tracking.revival_failed', [
                    'group_id' => $group->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Track the message attempt (happens regardless of send success/failure)
            try {
                $trackingService->recordMessage(
                    $group,
                    'revival.inactive',
                    "Revival message: inactive for {$daysInactive} days",
                    'activity_check',
                    null,
                    ['days_inactive' => $daysInactive, 'threshold' => $group->inactivity_threshold_days]
                );
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed to track message: {$e->getMessage()}");
            }

            // Update last_activity_at to prevent spam (will re-trigger after threshold)
            $group->update(['last_activity_at' => now()]);
        }

        return self::SUCCESS;
    }
}
