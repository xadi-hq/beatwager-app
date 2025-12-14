<?php

use App\Jobs\ApplyPointDecay;
use App\Jobs\ProcessEliminationAutoResolution;
use App\Jobs\ProcessSuperChallengeAutoApprovals;
use App\Jobs\ProcessSuperChallengeEligibility;
use App\Jobs\SendBettingClosedNotifications;
use App\Jobs\SendEliminationCountdownReminders;
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

// Send RSVP reminders (24h before deadline or event) - check hourly
Schedule::job(new \App\Jobs\SendEventRsvpReminders())
    ->hourly()
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

// Send betting closed notifications with bet reveals (every 5 minutes)
Schedule::job(new SendBettingClosedNotifications())
    ->everyFiveMinutes()
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

// Send birthday reminders (7 days before birthday)
Schedule::job(new \App\Jobs\SendBirthdayReminders())
    ->dailyAt('09:00')  // 9am - send after scheduled birthday messages
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

// Reconcile point balances (detect drift between cache and transaction ledger)
Schedule::command('points:reconcile')
    ->weeklyOn(1, '03:00')  // Every Monday at 3am
    ->withoutOverlapping()
    ->onOneServer();

// Check for groups eligible for SuperChallenge creation (daily)
Schedule::job(new ProcessSuperChallengeEligibility())
    ->dailyAt('10:00')  // 10am - good time for initiating challenges
    ->withoutOverlapping()
    ->onOneServer();

// Process SuperChallenge auto-approvals (48h timeout check)
Schedule::job(new ProcessSuperChallengeAutoApprovals())
    ->hourly()  // Check hourly for pending completions past 48h
    ->withoutOverlapping()
    ->onOneServer();

// Send elimination challenge countdown reminders (24h, 12h, 6h, 1h before deadline)
Schedule::job(new SendEliminationCountdownReminders())
    ->hourly()  // Check hourly for approaching deadlines
    ->withoutOverlapping()
    ->onOneServer();

// Process elimination challenge auto-resolution (deadline reached or insufficient participants)
Schedule::job(new ProcessEliminationAutoResolution())
    ->everyFifteenMinutes()  // Check frequently for deadline resolution
    ->withoutOverlapping()
    ->onOneServer();

// Process expired disputes (48h voting window passed)
Schedule::command('disputes:expire')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Send dispute vote reminders (24h before expiration)
Schedule::command('disputes:send-reminders')
    ->everyFourHours()  // Check every 4 hours for disputes needing reminders
    ->withoutOverlapping()
    ->onOneServer();