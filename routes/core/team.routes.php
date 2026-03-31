<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:' . config('app.dashboard_throttle', '120,1')])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('team')->as('team.')->group(static function () {
            Route::get('roster', [\App\Http\Controllers\Team\TeamController::class, 'index'])
                ->name('roster.index');

            Route::get('zones', [\App\Http\Controllers\Core\Team\ZoneController::class, 'index'])
                ->name('zones.index');
            Route::get('zones/create', [\App\Http\Controllers\Core\Team\ZoneController::class, 'create'])
                ->name('zones.create');
            Route::post('zones', [\App\Http\Controllers\Core\Team\ZoneController::class, 'store'])
                ->name('zones.store');
            Route::get('zones/{zone}', [\App\Http\Controllers\Core\Team\ZoneController::class, 'show'])
                ->name('zones.show');
            Route::get('zones/{zone}/edit', [\App\Http\Controllers\Core\Team\ZoneController::class, 'edit'])
                ->name('zones.edit');
            Route::put('zones/{zone}', [\App\Http\Controllers\Core\Team\ZoneController::class, 'update'])
                ->name('zones.update');
            Route::delete('zones/{zone}', [\App\Http\Controllers\Core\Team\ZoneController::class, 'destroy'])
                ->name('zones.destroy');

            Route::get('cleaners/{user}', [\App\Http\Controllers\Core\Team\CleanerProfileController::class, 'show'])
                ->name('cleaners.show');
            Route::get('cleaners/{user}/edit', [\App\Http\Controllers\Core\Team\CleanerProfileController::class, 'edit'])
                ->name('cleaners.edit');
            Route::put('cleaners/{user}', [\App\Http\Controllers\Core\Team\CleanerProfileController::class, 'update'])
                ->name('cleaners.update');

            Route::get('timesheets', [\App\Http\Controllers\Core\Team\WeeklyTimesheetController::class, 'index'])
                ->name('timesheets.index');
            Route::get('timesheets/{timesheet}', [\App\Http\Controllers\Core\Team\WeeklyTimesheetController::class, 'show'])
                ->name('timesheets.show');
            Route::post('timesheets/{timesheet}/submit', [\App\Http\Controllers\Core\Team\WeeklyTimesheetController::class, 'submit'])
                ->name('timesheets.submit');
            Route::post('timesheets/{timesheet}/approve', [\App\Http\Controllers\Core\Team\WeeklyTimesheetController::class, 'approve'])
                ->name('timesheets.approve');
            Route::post('timesheets/{timesheet}/reject', [\App\Http\Controllers\Core\Team\WeeklyTimesheetController::class, 'reject'])
                ->name('timesheets.reject');
        });
    });
