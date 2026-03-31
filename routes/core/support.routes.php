<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('support')->as('support.')->group(static function () {
            Route::get('issues', [\App\Http\Controllers\Core\Support\ServiceIssueController::class, 'index'])
                ->name('issues.index');
            Route::post('issues', [\App\Http\Controllers\Core\Support\ServiceIssueController::class, 'store'])
                ->name('issues.store');
            Route::get('issues/{issue}', [\App\Http\Controllers\Core\Support\ServiceIssueController::class, 'show'])
                ->name('issues.show');
            Route::put('issues/{issue}', [\App\Http\Controllers\Core\Support\ServiceIssueController::class, 'update'])
                ->name('issues.update');
            Route::delete('issues/{issue}', [\App\Http\Controllers\Core\Support\ServiceIssueController::class, 'destroy'])
                ->name('issues.destroy');
            Route::post('issues/{issue}/reply', [\App\Http\Controllers\Core\Support\ServiceIssueController::class, 'reply'])
                ->name('issues.reply');
            Route::post('issues/{issue}/note', [\App\Http\Controllers\Core\Support\ServiceIssueController::class, 'note'])
                ->name('issues.note');
        });
    });
