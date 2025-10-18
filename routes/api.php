<?php

use App\Http\Controllers\Api\SeasonController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook');

// Season management routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/groups/{group}/seasons', [SeasonController::class, 'index'])
        ->name('api.seasons.index');
    Route::get('/groups/{group}/seasons/{season}', [SeasonController::class, 'show'])
        ->name('api.seasons.show');
    Route::post('/groups/{group}/seasons', [SeasonController::class, 'store'])
        ->name('api.seasons.store');
    Route::post('/groups/{group}/seasons/end', [SeasonController::class, 'end'])
        ->name('api.seasons.end');
});
