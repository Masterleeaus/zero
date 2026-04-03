<?php

use Illuminate\Support\Facades\Route;
use Modules\BudgetAnalyticsForecasting\Http\Controllers\AdminSettingsController;

Route::middleware(['web','auth'])
    ->prefix('admin/budgets')
    ->group(function () {
        Route::get('/ai-settings', [AdminSettingsController::class, 'show'])->name('budgets.ai_settings');
    });
