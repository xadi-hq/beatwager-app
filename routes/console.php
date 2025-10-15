<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Send settlement reminders daily at 8am
Schedule::command('wagers:send-settlement-reminders')->dailyAt('08:00');