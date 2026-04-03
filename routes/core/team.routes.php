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

            // Service area hierarchy: regions
            Route::get('service-area-regions', [\App\Http\Controllers\Core\Team\ServiceAreaRegionController::class, 'index'])
                ->name('service-area-regions.index');
            Route::get('service-area-regions/create', [\App\Http\Controllers\Core\Team\ServiceAreaRegionController::class, 'create'])
                ->name('service-area-regions.create');
            Route::post('service-area-regions', [\App\Http\Controllers\Core\Team\ServiceAreaRegionController::class, 'store'])
                ->name('service-area-regions.store');
            Route::get('service-area-regions/{service_area_region}', [\App\Http\Controllers\Core\Team\ServiceAreaRegionController::class, 'show'])
                ->name('service-area-regions.show');
            Route::get('service-area-regions/{service_area_region}/edit', [\App\Http\Controllers\Core\Team\ServiceAreaRegionController::class, 'edit'])
                ->name('service-area-regions.edit');
            Route::put('service-area-regions/{service_area_region}', [\App\Http\Controllers\Core\Team\ServiceAreaRegionController::class, 'update'])
                ->name('service-area-regions.update');
            Route::delete('service-area-regions/{service_area_region}', [\App\Http\Controllers\Core\Team\ServiceAreaRegionController::class, 'destroy'])
                ->name('service-area-regions.destroy');

            // Service area hierarchy: districts
            Route::get('service-area-districts', [\App\Http\Controllers\Core\Team\ServiceAreaDistrictController::class, 'index'])
                ->name('service-area-districts.index');
            Route::get('service-area-districts/create', [\App\Http\Controllers\Core\Team\ServiceAreaDistrictController::class, 'create'])
                ->name('service-area-districts.create');
            Route::post('service-area-districts', [\App\Http\Controllers\Core\Team\ServiceAreaDistrictController::class, 'store'])
                ->name('service-area-districts.store');
            Route::get('service-area-districts/{service_area_district}', [\App\Http\Controllers\Core\Team\ServiceAreaDistrictController::class, 'show'])
                ->name('service-area-districts.show');
            Route::get('service-area-districts/{service_area_district}/edit', [\App\Http\Controllers\Core\Team\ServiceAreaDistrictController::class, 'edit'])
                ->name('service-area-districts.edit');
            Route::put('service-area-districts/{service_area_district}', [\App\Http\Controllers\Core\Team\ServiceAreaDistrictController::class, 'update'])
                ->name('service-area-districts.update');
            Route::delete('service-area-districts/{service_area_district}', [\App\Http\Controllers\Core\Team\ServiceAreaDistrictController::class, 'destroy'])
                ->name('service-area-districts.destroy');

            // Service area hierarchy: branches
            Route::get('service-area-branches', [\App\Http\Controllers\Core\Team\ServiceAreaBranchController::class, 'index'])
                ->name('service-area-branches.index');
            Route::get('service-area-branches/create', [\App\Http\Controllers\Core\Team\ServiceAreaBranchController::class, 'create'])
                ->name('service-area-branches.create');
            Route::post('service-area-branches', [\App\Http\Controllers\Core\Team\ServiceAreaBranchController::class, 'store'])
                ->name('service-area-branches.store');
            Route::get('service-area-branches/{service_area_branch}', [\App\Http\Controllers\Core\Team\ServiceAreaBranchController::class, 'show'])
                ->name('service-area-branches.show');
            Route::get('service-area-branches/{service_area_branch}/edit', [\App\Http\Controllers\Core\Team\ServiceAreaBranchController::class, 'edit'])
                ->name('service-area-branches.edit');
            Route::put('service-area-branches/{service_area_branch}', [\App\Http\Controllers\Core\Team\ServiceAreaBranchController::class, 'update'])
                ->name('service-area-branches.update');
            Route::delete('service-area-branches/{service_area_branch}', [\App\Http\Controllers\Core\Team\ServiceAreaBranchController::class, 'destroy'])
                ->name('service-area-branches.destroy');

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

            // Staff profiles (HRM)
            Route::get('staff-profiles', [\App\Http\Controllers\Core\Team\StaffProfileController::class, 'index'])
                ->name('staff-profiles.index');
            Route::get('staff-profiles/create', [\App\Http\Controllers\Core\Team\StaffProfileController::class, 'create'])
                ->name('staff-profiles.create');
            Route::post('staff-profiles', [\App\Http\Controllers\Core\Team\StaffProfileController::class, 'store'])
                ->name('staff-profiles.store');
            Route::get('staff-profiles/{staff_profile}', [\App\Http\Controllers\Core\Team\StaffProfileController::class, 'show'])
                ->name('staff-profiles.show');
            Route::get('staff-profiles/{staff_profile}/edit', [\App\Http\Controllers\Core\Team\StaffProfileController::class, 'edit'])
                ->name('staff-profiles.edit');
            Route::put('staff-profiles/{staff_profile}', [\App\Http\Controllers\Core\Team\StaffProfileController::class, 'update'])
                ->name('staff-profiles.update');
            Route::delete('staff-profiles/{staff_profile}', [\App\Http\Controllers\Core\Team\StaffProfileController::class, 'destroy'])
                ->name('staff-profiles.destroy');

            // ── MODULE_02: Capability Registry ───────────────────────────────
            Route::prefix('capabilities')->as('capabilities.')->group(static function () {
                Route::get('profile', [\App\Http\Controllers\Team\CapabilityController::class, 'profile'])
                    ->name('profile');
                Route::get('skills', [\App\Http\Controllers\Team\CapabilityController::class, 'skills'])
                    ->name('skills');
                Route::get('certifications', [\App\Http\Controllers\Team\CapabilityController::class, 'certifications'])
                    ->name('certifications');
                Route::get('availability', [\App\Http\Controllers\Team\CapabilityController::class, 'availability'])
                    ->name('availability');
                Route::get('gaps', [\App\Http\Controllers\Team\CapabilityController::class, 'gaps'])
                    ->name('gaps');
            });
        });
    });
