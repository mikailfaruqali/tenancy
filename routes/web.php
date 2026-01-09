<?php

use Illuminate\Support\Facades\Route;
use Snawbar\Guardian\Controllers\GuardianController;

Route::middleware(['web', 'auth'])->prefix('guardian')->name('guardian.')->group(function () {
    Route::get('/email', [GuardianController::class, 'showEmail'])->name('email');
    Route::post('/email/send', [GuardianController::class, 'sendEmail'])
        ->middleware('throttle:5,1')
        ->name('email.send');
    Route::post('/email/verify', [GuardianController::class, 'verifyEmail'])
        ->middleware('throttle:5,1')
        ->name('email.verify');
    Route::get('/authenticator', [GuardianController::class, 'showAuthenticator'])->name('authenticator');
    Route::post('/authenticator/verify', [GuardianController::class, 'verifyAuthenticator'])
        ->middleware('throttle:5,1')
        ->name('authenticator.verify');
});
