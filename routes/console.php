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