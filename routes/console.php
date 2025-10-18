<?php

use App\Jobs\ApplyPointDecay;
use App\Jobs\SendEventAttendancePrompts;
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