<?php

use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EliminationChallengeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupSettingsController;
use App\Http\Controllers\LLMModelsController;
use App\Http\Controllers\ScheduledMessageController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\SuperChallengeController;
use App\Http\Controllers\WagerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Public help documentation
Route::get('/help', function () {
    return Inertia::render('Help');
})->name('help');

// Short URL redirects
Route::get('/l/{code}', [ShortUrlController::class, 'redirect'])->name('short.redirect');

// Routes requiring authentication via signed URL (platform-agnostic: Telegram, Discord, etc.)
// First visit: validates signed URL and establishes session
// Subsequent visits: uses session (clean URLs, no tokens)
Route::middleware(['signed.auth'])->group(function () {
    // User dashboard routes
    Route::get('/me', [DashboardController::class, 'show'])->name('dashboard.me');
    Route::post('/me/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');

    // Wager creation routes
    Route::get('/wager/create', [WagerController::class, 'create'])->name('wager.create');
    Route::post('/wager/store', [WagerController::class, 'store'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('wager.store');
    Route::get('/wager/success/{wager}', [WagerController::class, 'success'])->name('wager.success');

    // Wager join routes (complex input types)
    Route::get('/wager/{wager}/join', [WagerController::class, 'showJoinForm'])
        ->middleware('group.member')
        ->name('wager.join');
    Route::post('/wager/{wager}/join', [WagerController::class, 'submitJoin'])
        ->middleware(['throttle:20,1', 'group.member'])
        ->name('wager.join.submit');
    Route::get('/wager/join/success/{entry}', [WagerController::class, 'joinSuccess'])->name('wager.join.success');

    // Wager settlement routes
    Route::get('/wager/settle', [WagerController::class, 'showSettlementForm'])->name('wager.settle');
    Route::post('/wager/settle', [WagerController::class, 'settle'])
        ->middleware('throttle:10,1')
        ->name('wager.settle.submit');
    Route::get('/wager/settle/success/{wager}', [WagerController::class, 'settlementSuccess'])->name('wager.settle.success');

    // Wager landing page (view progress)
    Route::get('/wager/{wager}', [WagerController::class, 'show'])
        ->middleware('group.member')
        ->name('wager.show');
    Route::post('/wager/{wager}/settle', [WagerController::class, 'settleFromShow'])
        ->middleware('throttle:10,1')
        ->name('wager.settle.fromshow');

    // Event routes
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events/store', [EventController::class, 'store'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])
        ->middleware('group.member')
        ->name('events.show');
    Route::get('/events/{event}/attendance', [EventController::class, 'attendance'])
        ->middleware('group.member')
        ->name('events.attendance');
    Route::post('/events/{event}/attendance', [EventController::class, 'recordAttendance'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('events.recordAttendance');
    Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
    Route::post('/events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');

    // Challenge routes
    Route::get('/challenges/create', [ChallengeController::class, 'create'])->name('challenges.create');
    Route::post('/challenges/store', [ChallengeController::class, 'store'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('challenges.store');
    Route::get('/challenges/success/{challenge}', [ChallengeController::class, 'success'])->name('challenge.success');
    Route::get('/challenges/{challenge}', [ChallengeController::class, 'show'])
        ->middleware('group.member')
        ->name('challenges.show');
    Route::post('/challenges/{challenge}/accept', [ChallengeController::class, 'accept'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('challenges.accept');
    Route::post('/challenges/{challenge}/submit', [ChallengeController::class, 'submit'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('challenges.submit');
    Route::post('/challenges/{challenge}/approve', [ChallengeController::class, 'approve'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('challenges.approve');
    Route::post('/challenges/{challenge}/reject', [ChallengeController::class, 'reject'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('challenges.reject');
    Route::post('/challenges/{challenge}/cancel', [ChallengeController::class, 'cancel'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('challenges.cancel');

    // Elimination Challenge routes
    Route::post('/challenges/store-elimination', [ChallengeController::class, 'storeElimination'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('challenges.storeElimination');
    Route::get('/elimination/success/{challenge}', [ChallengeController::class, 'eliminationSuccess'])
        ->name('elimination.success');

    // Elimination Challenge participation routes
    Route::get('/elimination/{challenge}', [EliminationChallengeController::class, 'show'])
        ->middleware('group.member')
        ->name('elimination.show');
    Route::get('/elimination/{challenge}/tap-in', [EliminationChallengeController::class, 'showTapIn'])
        ->middleware('group.member')
        ->name('elimination.tap-in');
    Route::post('/elimination/{challenge}/tap-in', [EliminationChallengeController::class, 'tapIn'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('elimination.tap-in.submit');
    Route::get('/elimination/{challenge}/tap-in/success', [EliminationChallengeController::class, 'tapInSuccess'])
        ->name('elimination.tap-in.success');
    Route::get('/elimination/{challenge}/tap-out', [EliminationChallengeController::class, 'showTapOut'])
        ->middleware('group.member')
        ->name('elimination.tap-out');
    Route::post('/elimination/{challenge}/tap-out', [EliminationChallengeController::class, 'tapOut'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('elimination.tap-out.submit');
    Route::get('/elimination/{challenge}/tap-out/success', [EliminationChallengeController::class, 'tapOutSuccess'])
        ->name('elimination.tap-out.success');
    Route::post('/elimination/{challenge}/cancel', [EliminationChallengeController::class, 'cancel'])
        ->middleware(['throttle:10,1', 'group.member'])
        ->name('elimination.cancel');
    Route::get('/elimination/{challenge}/cancelled', [EliminationChallengeController::class, 'cancelled'])
        ->name('elimination.cancelled');

    // SuperChallenge routes
    Route::get('/superchallenge/nudge/{nudge}/respond', [SuperChallengeController::class, 'respondToNudge'])
        ->name('superchallenge.nudge.respond');
    Route::post('/superchallenge/create', [SuperChallengeController::class, 'create'])
        ->middleware('throttle:5,1')
        ->name('superchallenge.create');
    Route::get('/superchallenge/created/{challenge}', [SuperChallengeController::class, 'created'])
        ->name('superchallenge.created');
    Route::get('/superchallenge/{challenge}/accept', [SuperChallengeController::class, 'accept'])
        ->middleware('group.member')
        ->name('superchallenge.accept');
    Route::get('/superchallenge/accepted/{challenge}', [SuperChallengeController::class, 'accepted'])
        ->name('superchallenge.accepted');
    Route::match(['get', 'post'], '/superchallenge/{challenge}/complete', [SuperChallengeController::class, 'complete'])
        ->middleware('group.member')
        ->name('superchallenge.complete');
    Route::get('/superchallenge/completed/{challenge}', [SuperChallengeController::class, 'completed'])
        ->name('superchallenge.completed');
    Route::get('/superchallenge/participant/{participant}/validate', [SuperChallengeController::class, 'validate'])
        ->name('superchallenge.validate');
    Route::get('/superchallenge/validated/{participant}', [SuperChallengeController::class, 'validated'])
        ->name('superchallenge.validated');

    // Group routes
    Route::get('/groups/{group}', [GroupController::class, 'show'])
        ->middleware('group.member')
        ->name('groups.show');
    Route::post('/groups/{group}/settings', [GroupSettingsController::class, 'update'])
        ->middleware(['throttle:5,1', 'group.member'])
        ->name('groups.settings.update');

    // LLM models routes
    Route::get('/groups/{group}/llm-models', [LLMModelsController::class, 'index'])
        ->middleware('group.member')
        ->name('groups.llm-models');
    Route::get('/llm-models/defaults', [LLMModelsController::class, 'defaults'])
        ->name('llm-models.defaults');

    // Season management routes
    Route::get('/groups/{group}/seasons', [SeasonController::class, 'index'])->name('seasons.index');
    Route::get('/groups/{group}/seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
    Route::post('/groups/{group}/seasons', [SeasonController::class, 'store'])->name('seasons.store');
    Route::post('/groups/{group}/seasons/end', [SeasonController::class, 'end'])->name('seasons.end');

    // Streak configuration routes
    Route::get('/groups/{group}/streak-config', [\App\Http\Controllers\GroupStreakController::class, 'show'])->name('groups.streak.show');
    Route::post('/groups/{group}/streak-config', [\App\Http\Controllers\GroupStreakController::class, 'store'])->name('groups.streak.store');

    // Scheduled messages routes
    Route::get('/groups/{group}/messages', [ScheduledMessageController::class, 'index'])->name('messages.index');
    Route::get('/groups/{group}/messages/birthdays/suggestions', [ScheduledMessageController::class, 'birthdaySuggestions'])->name('messages.birthdays.suggestions');
    Route::get('/groups/{group}/messages/{scheduledMessage}', [ScheduledMessageController::class, 'show'])->name('messages.show');
    Route::post('/groups/{group}/messages', [ScheduledMessageController::class, 'store'])->name('messages.store');
    Route::put('/groups/{group}/messages/{scheduledMessage}', [ScheduledMessageController::class, 'update'])->name('messages.update');
    Route::post('/groups/{group}/messages/{scheduledMessage}/toggle', [ScheduledMessageController::class, 'toggleActive'])->name('messages.toggle');
    Route::delete('/groups/{group}/messages/{scheduledMessage}', [ScheduledMessageController::class, 'destroy'])->name('messages.destroy');

    // Donation routes
    Route::get('/donations/create', [DonationController::class, 'create'])->name('donations.create');
    Route::get('/donations/groups/{group}/recipients', [DonationController::class, 'recipients'])->name('donations.recipients');
    Route::post('/donations', [DonationController::class, 'store'])->name('donations.store');

    // Dispute routes
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])
        ->name('disputes.show');
    Route::post('/wager/{wager}/dispute', [DisputeController::class, 'createWagerDispute'])
        ->middleware(['throttle:5,1', 'group.member'])
        ->name('disputes.create.wager');
    Route::post('/elimination/{challenge}/dispute', [DisputeController::class, 'createEliminationDispute'])
        ->middleware(['throttle:5,1', 'group.member'])
        ->name('disputes.create.elimination');
    Route::post('/disputes/{dispute}/vote', [DisputeController::class, 'vote'])
        ->middleware('throttle:10,1')
        ->name('disputes.vote');
});
