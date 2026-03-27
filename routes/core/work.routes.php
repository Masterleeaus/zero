<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('work')->as('work.')->group(static function () {
            Route::get('sites', [\App\Http\Controllers\Core\Work\SiteController::class, 'index'])
                ->name('sites.index');
            Route::get('sites/{site}', [\App\Http\Controllers\Core\Work\SiteController::class, 'show'])
                ->name('sites.show');

            Route::get('service-jobs', [\App\Http\Controllers\Core\Work\ServiceJobController::class, 'index'])
                ->name('service-jobs.index');
            Route::get('service-jobs/{job}', [\App\Http\Controllers\Core\Work\ServiceJobController::class, 'show'])
                ->name('service-jobs.show');

            Route::get('checklists', [\App\Http\Controllers\Core\Work\ChecklistController::class, 'index'])
                ->name('checklists.index');
            Route::get('checklists/{checklist}', [\App\Http\Controllers\Core\Work\ChecklistController::class, 'show'])
                ->name('checklists.show');
        });
    });
