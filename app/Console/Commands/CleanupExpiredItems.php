<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Models\GroupEvent;
use App\Models\Wager;
use Illuminate\Console\Command;

class CleanupExpiredItems extends Command
{
    protected $signature = 'cleanup:expired-items
                            {--dry-run : Preview items to be deleted without actually deleting}';

    protected $description = 'Delete expired wagers, challenges, and events with no engagement';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No items will be deleted');
        }

        $this->info('🧹 Starting cleanup of expired items...');
        $this->newLine();

        // Cleanup expired wagers
        $expiredWagers = Wager::expired()->get();
        $this->info("📊 Found {$expiredWagers->count()} expired wagers (status='open', past deadline, 0 participants)");

        if (!$isDryRun && $expiredWagers->count() > 0) {
            foreach ($expiredWagers as $wager) {
                $this->line("  ❌ Deleting wager: {$wager->title} (ID: {$wager->id})");
            }
            $deletedWagers = Wager::expired()->delete();
            $this->info("  ✅ Deleted {$deletedWagers} wagers");
        } elseif ($expiredWagers->count() > 0) {
            foreach ($expiredWagers as $wager) {
                $this->line("  📋 Would delete: {$wager->title} (ID: {$wager->id})");
            }
        }

        $this->newLine();

        // Cleanup expired challenges
        $expiredChallenges = Challenge::expired()->get();
        $this->info("🎯 Found {$expiredChallenges->count()} expired challenges (status='open', past deadline, no acceptor)");

        if (!$isDryRun && $expiredChallenges->count() > 0) {
            foreach ($expiredChallenges as $challenge) {
                $this->line("  ❌ Deleting challenge: {$challenge->description} (ID: {$challenge->id})");
            }
            $deletedChallenges = Challenge::expired()->delete();
            $this->info("  ✅ Deleted {$deletedChallenges} challenges");
        } elseif ($expiredChallenges->count() > 0) {
            foreach ($expiredChallenges as $challenge) {
                $this->line("  📋 Would delete: {$challenge->description} (ID: {$challenge->id})");
            }
        }

        $this->newLine();

        // Cleanup expired events
        $expiredEvents = GroupEvent::expired()->get();
        $this->info("📅 Found {$expiredEvents->count()} expired events (past RSVP/event date, 0 RSVPs)");

        if (!$isDryRun && $expiredEvents->count() > 0) {
            foreach ($expiredEvents as $event) {
                $this->line("  ❌ Deleting event: {$event->name} (ID: {$event->id})");
            }
            $deletedEvents = GroupEvent::expired()->delete();
            $this->info("  ✅ Deleted {$deletedEvents} events");
        } elseif ($expiredEvents->count() > 0) {
            foreach ($expiredEvents as $event) {
                $this->line("  📋 Would delete: {$event->name} (ID: {$event->id})");
            }
        }

        $this->newLine();

        // Summary
        $totalFound = $expiredWagers->count() + $expiredChallenges->count() + $expiredEvents->count();

        if ($isDryRun) {
            $this->info("✨ Dry run complete. Would delete {$totalFound} items total.");
        } else {
            $this->info("✨ Cleanup complete. Deleted {$totalFound} expired items total.");
        }

        return self::SUCCESS;
    }
}
