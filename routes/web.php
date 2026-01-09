<?php

use Illuminate\Support\Facades\Route;
use Snawbar\Tenancy\Controllers\TenancyController;
use Snawbar\Tenancy\Middleware\EnsureMainTenancy;

Route::middleware([EnsureMainTenancy::class])->prefix('snawbar-tenancy')->name('tenancy.')->group(function () {
    Route::get('create-view', [TenancyController::class, 'createView'])->name('create.view');
    Route::post('create', [TenancyController::class, 'create'])->name('create');
});
