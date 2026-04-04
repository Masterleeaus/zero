<?php

declare(strict_types=1);

use App\Http\Controllers\Finance\JobFinanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('dashboard/finance')->name('dashboard.finance.')->group(function () {
    Route::get('/jobs/{job}/summary', [JobFinanceController::class, 'summary'])->name('jobs.summary');
    Route::get('/jobs/{job}/costs', [JobFinanceController::class, 'costs'])->name('jobs.costs');
    Route::get('/jobs/{job}/revenue', [JobFinanceController::class, 'revenue'])->name('jobs.revenue');
    Route::get('/at-risk', [JobFinanceController::class, 'atRisk'])->name('at-risk');
    Route::post('/rollup', [JobFinanceController::class, 'rollup'])->name('rollup');
});
