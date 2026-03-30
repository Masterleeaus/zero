<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('work')->as('work.')->group(static function () {
            Route::get('sites', [\App\Http\Controllers\Core\Work\SiteController::class, 'index'])
                ->name('sites.index');
            Route::get('sites/create', [\App\Http\Controllers\Core\Work\SiteController::class, 'create'])
                ->name('sites.create');
            Route::post('sites', [\App\Http\Controllers\Core\Work\SiteController::class, 'store'])
                ->name('sites.store');
            Route::get('sites/{site}', [\App\Http\Controllers\Core\Work\SiteController::class, 'show'])
                ->name('sites.show');
            Route::get('sites/{site}/edit', [\App\Http\Controllers\Core\Work\SiteController::class, 'edit'])
                ->name('sites.edit');
            Route::put('sites/{site}', [\App\Http\Controllers\Core\Work\SiteController::class, 'update'])
                ->name('sites.update');

            Route::get('service-jobs', [\App\Http\Controllers\Core\Work\ServiceJobController::class, 'index'])
                ->name('service-jobs.index');
            Route::get('service-jobs/create', [\App\Http\Controllers\Core\Work\ServiceJobController::class, 'create'])
                ->name('service-jobs.create');
            Route::post('service-jobs', [\App\Http\Controllers\Core\Work\ServiceJobController::class, 'store'])
                ->name('service-jobs.store');
            Route::get('service-jobs/{job}', [\App\Http\Controllers\Core\Work\ServiceJobController::class, 'show'])
                ->name('service-jobs.show');
            Route::get('service-jobs/{job}/edit', [\App\Http\Controllers\Core\Work\ServiceJobController::class, 'edit'])
                ->name('service-jobs.edit');
            Route::put('service-jobs/{job}', [\App\Http\Controllers\Core\Work\ServiceJobController::class, 'update'])
                ->name('service-jobs.update');

            Route::get('checklists', [\App\Http\Controllers\Core\Work\ChecklistController::class, 'index'])
                ->name('checklists.index');
            Route::get('checklists/create', [\App\Http\Controllers\Core\Work\ChecklistController::class, 'create'])
                ->name('checklists.create');
            Route::post('checklists', [\App\Http\Controllers\Core\Work\ChecklistController::class, 'store'])
                ->name('checklists.store');
            Route::get('checklists/{checklist}', [\App\Http\Controllers\Core\Work\ChecklistController::class, 'show'])
                ->name('checklists.show');
            Route::get('checklists/{checklist}/edit', [\App\Http\Controllers\Core\Work\ChecklistController::class, 'edit'])
                ->name('checklists.edit');
            Route::put('checklists/{checklist}', [\App\Http\Controllers\Core\Work\ChecklistController::class, 'update'])
                ->name('checklists.update');

            Route::get('timelogs', [\App\Http\Controllers\Core\Work\TimelogController::class, 'index'])
                ->name('timelogs.index');
            Route::get('timelogs/create', [\App\Http\Controllers\Core\Work\TimelogController::class, 'create'])
                ->name('timelogs.create');
            Route::post('timelogs', [\App\Http\Controllers\Core\Work\TimelogController::class, 'store'])
                ->name('timelogs.store');
            Route::post('timelogs/{timelog}/stop', [\App\Http\Controllers\Core\Work\TimelogController::class, 'stop'])
                ->name('timelogs.stop');

            Route::get('attendance', [\App\Http\Controllers\Core\Work\AttendanceController::class, 'index'])
                ->name('attendance.index');
            Route::get('attendance/create', [\App\Http\Controllers\Core\Work\AttendanceController::class, 'create'])
                ->name('attendance.create');
            Route::post('attendance', [\App\Http\Controllers\Core\Work\AttendanceController::class, 'store'])
                ->name('attendance.store');
            Route::post('attendance/{attendance}/checkout', [\App\Http\Controllers\Core\Work\AttendanceController::class, 'checkout'])
                ->name('attendance.checkout');

            Route::get('agreements', [\App\Http\Controllers\Core\Work\ServiceAgreementController::class, 'index'])
                ->name('agreements.index');
            Route::get('agreements/create', [\App\Http\Controllers\Core\Work\ServiceAgreementController::class, 'create'])
                ->name('agreements.create');
            Route::post('agreements', [\App\Http\Controllers\Core\Work\ServiceAgreementController::class, 'store'])
                ->name('agreements.store');
            Route::get('agreements/{agreement}', [\App\Http\Controllers\Core\Work\ServiceAgreementController::class, 'show'])
                ->name('agreements.show');
        });
    });
