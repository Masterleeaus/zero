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

            Route::get('regions', [\App\Http\Controllers\Core\Team\RegionController::class, 'index'])
                ->name('regions.index');
            Route::get('regions/create', [\App\Http\Controllers\Core\Team\RegionController::class, 'create'])
                ->name('regions.create');
            Route::post('regions', [\App\Http\Controllers\Core\Team\RegionController::class, 'store'])
                ->name('regions.store');
            Route::get('regions/{region}', [\App\Http\Controllers\Core\Team\RegionController::class, 'show'])
                ->name('regions.show');
            Route::get('regions/{region}/edit', [\App\Http\Controllers\Core\Team\RegionController::class, 'edit'])
                ->name('regions.edit');
            Route::put('regions/{region}', [\App\Http\Controllers\Core\Team\RegionController::class, 'update'])
                ->name('regions.update');
            Route::delete('regions/{region}', [\App\Http\Controllers\Core\Team\RegionController::class, 'destroy'])
                ->name('regions.destroy');

            Route::get('districts', [\App\Http\Controllers\Core\Team\DistrictController::class, 'index'])
                ->name('districts.index');
            Route::get('districts/create', [\App\Http\Controllers\Core\Team\DistrictController::class, 'create'])
                ->name('districts.create');
            Route::post('districts', [\App\Http\Controllers\Core\Team\DistrictController::class, 'store'])
                ->name('districts.store');
            Route::get('districts/{district}', [\App\Http\Controllers\Core\Team\DistrictController::class, 'show'])
                ->name('districts.show');
            Route::get('districts/{district}/edit', [\App\Http\Controllers\Core\Team\DistrictController::class, 'edit'])
                ->name('districts.edit');
            Route::put('districts/{district}', [\App\Http\Controllers\Core\Team\DistrictController::class, 'update'])
                ->name('districts.update');
            Route::delete('districts/{district}', [\App\Http\Controllers\Core\Team\DistrictController::class, 'destroy'])
                ->name('districts.destroy');

            Route::get('branches', [\App\Http\Controllers\Core\Team\BranchController::class, 'index'])
                ->name('branches.index');
            Route::get('branches/create', [\App\Http\Controllers\Core\Team\BranchController::class, 'create'])
                ->name('branches.create');
            Route::post('branches', [\App\Http\Controllers\Core\Team\BranchController::class, 'store'])
                ->name('branches.store');
            Route::get('branches/{branch}', [\App\Http\Controllers\Core\Team\BranchController::class, 'show'])
                ->name('branches.show');
            Route::get('branches/{branch}/edit', [\App\Http\Controllers\Core\Team\BranchController::class, 'edit'])
                ->name('branches.edit');
            Route::put('branches/{branch}', [\App\Http\Controllers\Core\Team\BranchController::class, 'update'])
                ->name('branches.update');
            Route::delete('branches/{branch}', [\App\Http\Controllers\Core\Team\BranchController::class, 'destroy'])
                ->name('branches.destroy');

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
