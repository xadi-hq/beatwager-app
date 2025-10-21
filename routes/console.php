<?php

use App\Jobs\ApplyPointDecay;
use App\Jobs\SendEngagementPrompts;
use App\Jobs\SendEventAttendancePrompts;
use App\Jobs\SendSeasonMilestoneDrops;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Send settlement reminders daily at 8am
Schedule::command('wagers:send-settlement-reminders')->dailyAt('08:00');

// Apply point decay daily at 1am
Schedule::job(new ApplyPointDecay())
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();

// Check for events needing attendance prompts (hourly)
Schedule::job(new SendEventAttendancePrompts())
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Send engagement prompts for stale wagers (hourly)
Schedule::job(new SendEngagementPrompts())
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Aggregate LLM metrics from logs daily at midnight
Schedule::command('llm:aggregate')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->onOneServer();

// Activity Tracking: Check for inactive groups and send revival messages
// COMMENTED OUT - Uncomment when FEATURE_ACTIVITY_TRACKING=true is set
// The command itself checks the feature flag, but this prevents unnecessary scheduling overhead
Schedule::command('activity:check')
    ->dailyAt('09:00')  // 9am - good time for engagement
    ->withoutOverlapping()
    ->onOneServer();

// Check for seasons that should end (based on season_ends_at)
Schedule::command('seasons:check')
    ->dailyAt('00:01')  // Just after midnight
    ->withoutOverlapping()
    ->onOneServer();

// Send scheduled messages (holidays, birthdays, custom dates)
Schedule::command('messages:send-scheduled')
    ->dailyAt('08:00')  // 8am - good time for special occasion messages
    ->withoutOverlapping()
    ->onOneServer();

// Check for season milestone drops (50%, 75%, 90% progress)
Schedule::job(new SendSeasonMilestoneDrops())
    ->dailyAt('12:00')  // Noon - good time for surprise drops
    ->withoutOverlapping()
    ->onOneServer();

// Cleanup expired items with no engagement (wagers, challenges, events)
Schedule::command('cleanup:expired-items')
    ->dailyAt('02:00')  // 2am - low traffic time for cleanup
    ->withoutOverlapping()
    ->onOneServer();