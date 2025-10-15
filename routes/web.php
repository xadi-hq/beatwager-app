<?php

use App\Http\Controllers\DashboardController;
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
});
