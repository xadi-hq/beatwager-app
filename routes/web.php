<?php

use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupSettingsController;
use App\Http\Controllers\ScheduledMessageController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\WagerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Short URL redirects
Route::get('/l/{code}', [ShortUrlController::class, 'redirect'])->name('short.redirect');

// Routes requiring authentication via signed URL (platform-agnostic: Telegram, Discord, etc.)
// First visit: validates signed URL and establishes session
// Subsequent visits: uses session (clean URLs, no tokens)
Route::middleware(['signed.auth'])->group(function () {
    // User dashboard routes
    Route::get('/me', [DashboardController::class, 'show'])->name('dashboard.me');
    Route::post('/me/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');

    // Help page
    Route::get('/help', function () {
        return Inertia::render('Help');
    })->name('help');

    // Wager creation routes
    Route::get('/wager/create', [WagerController::class, 'create'])->name('wager.create');
    Route::post('/wager/store', [WagerController::class, 'store'])->name('wager.store');
    Route::get('/wager/success/{wager}', [WagerController::class, 'success'])->name('wager.success');

    // Wager settlement routes
    Route::get('/wager/settle', [WagerController::class, 'showSettlementForm'])->name('wager.settle');
    Route::post('/wager/settle', [WagerController::class, 'settle'])->name('wager.settle.submit');
    Route::get('/wager/settle/success/{wager}', [WagerController::class, 'settlementSuccess'])->name('wager.settle.success');

    // Wager landing page (view progress)
    Route::get('/wager/{wager}', [WagerController::class, 'show'])->name('wager.show');
    Route::post('/wager/{wager}/settle', [WagerController::class, 'settleFromShow'])->name('wager.settle.fromshow');

    // Event routes
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/attendance', [EventController::class, 'attendance'])->name('events.attendance');
    Route::post('/events/{event}/attendance', [EventController::class, 'recordAttendance'])->name('events.recordAttendance');
    Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');

    // Challenge routes
    Route::get('/challenges/create', [ChallengeController::class, 'create'])->name('challenges.create');
    Route::post('/challenges/store', [ChallengeController::class, 'store'])->name('challenges.store');
    Route::get('/challenges/success/{challenge}', [ChallengeController::class, 'success'])->name('challenge.success');
    Route::get('/challenges/{challenge}', [ChallengeController::class, 'show'])->name('challenges.show');
    Route::post('/challenges/{challenge}/accept', [ChallengeController::class, 'accept'])->name('challenges.accept');
    Route::post('/challenges/{challenge}/submit', [ChallengeController::class, 'submit'])->name('challenges.submit');
    Route::post('/challenges/{challenge}/approve', [ChallengeController::class, 'approve'])->name('challenges.approve');
    Route::post('/challenges/{challenge}/reject', [ChallengeController::class, 'reject'])->name('challenges.reject');
    Route::post('/challenges/{challenge}/cancel', [ChallengeController::class, 'cancel'])->name('challenges.cancel');

    // Group routes
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{group}/settings', [GroupSettingsController::class, 'update'])->name('groups.settings.update');

    // Season management routes
    Route::get('/groups/{group}/seasons', [SeasonController::class, 'index'])->name('seasons.index');
    Route::get('/groups/{group}/seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
    Route::post('/groups/{group}/seasons', [SeasonController::class, 'store'])->name('seasons.store');
    Route::post('/groups/{group}/seasons/end', [SeasonController::class, 'end'])->name('seasons.end');

    // Scheduled messages routes
    Route::get('/groups/{group}/messages', [ScheduledMessageController::class, 'index'])->name('messages.index');
    Route::get('/groups/{group}/messages/{scheduledMessage}', [ScheduledMessageController::class, 'show'])->name('messages.show');
    Route::post('/groups/{group}/messages', [ScheduledMessageController::class, 'store'])->name('messages.store');
    Route::put('/groups/{group}/messages/{scheduledMessage}', [ScheduledMessageController::class, 'update'])->name('messages.update');
    Route::post('/groups/{group}/messages/{scheduledMessage}/toggle', [ScheduledMessageController::class, 'toggleActive'])->name('messages.toggle');
    Route::delete('/groups/{group}/messages/{scheduledMessage}', [ScheduledMessageController::class, 'destroy'])->name('messages.destroy');
});
