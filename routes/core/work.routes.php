<?php

use App\Http\Controllers\Core\Work\DispatchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:' . config('throttle.dashboard', '120,1')])
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
            Route::post('service-jobs/{job}/create-invoice', [\App\Http\Controllers\Core\Work\ServiceJobController::class, 'createInvoice'])
                ->name('service-jobs.create-invoice');

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

            Route::get('shifts', [\App\Http\Controllers\Core\Work\ShiftController::class, 'index'])
                ->name('shifts.index');
            Route::get('shifts/create', [\App\Http\Controllers\Core\Work\ShiftController::class, 'create'])
                ->name('shifts.create');
            Route::post('shifts', [\App\Http\Controllers\Core\Work\ShiftController::class, 'store'])
                ->name('shifts.store');
            Route::get('shifts/{shift}', [\App\Http\Controllers\Core\Work\ShiftController::class, 'show'])
                ->name('shifts.show');
            Route::post('shifts/{shift}/assign-job', [\App\Http\Controllers\Core\Work\ShiftController::class, 'assignJobToShift'])
                ->name('shifts.assign');

            Route::get('leaves', [\App\Http\Controllers\Core\Work\LeaveController::class, 'index'])
                ->name('leaves.index');
            Route::get('leaves/create', [\App\Http\Controllers\Core\Work\LeaveController::class, 'create'])
                ->name('leaves.create');
            Route::post('leaves', [\App\Http\Controllers\Core\Work\LeaveController::class, 'store'])
                ->name('leaves.store');
            Route::get('leaves/{leave}', [\App\Http\Controllers\Core\Work\LeaveController::class, 'show'])
                ->name('leaves.show');
            Route::get('leaves/{leave}/edit', [\App\Http\Controllers\Core\Work\LeaveController::class, 'edit'])
                ->name('leaves.edit');
            Route::put('leaves/{leave}', [\App\Http\Controllers\Core\Work\LeaveController::class, 'update'])
                ->name('leaves.update');
            Route::delete('leaves/{leave}', [\App\Http\Controllers\Core\Work\LeaveController::class, 'destroy'])
                ->name('leaves.destroy');

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
            Route::post('agreements/{agreement}/run', [\App\Http\Controllers\Core\Work\ServiceAgreementController::class, 'run'])
                ->name('agreements.run');

            // Job Stages
            Route::get('job-stages', [\App\Http\Controllers\Core\Work\JobStageController::class, 'index'])
                ->name('job-stages.index');
            Route::get('job-stages/create', [\App\Http\Controllers\Core\Work\JobStageController::class, 'create'])
                ->name('job-stages.create');
            Route::post('job-stages', [\App\Http\Controllers\Core\Work\JobStageController::class, 'store'])
                ->name('job-stages.store');
            Route::get('job-stages/{jobStage}', [\App\Http\Controllers\Core\Work\JobStageController::class, 'show'])
                ->name('job-stages.show');
            Route::get('job-stages/{jobStage}/edit', [\App\Http\Controllers\Core\Work\JobStageController::class, 'edit'])
                ->name('job-stages.edit');
            Route::put('job-stages/{jobStage}', [\App\Http\Controllers\Core\Work\JobStageController::class, 'update'])
                ->name('job-stages.update');
            Route::delete('job-stages/{jobStage}', [\App\Http\Controllers\Core\Work\JobStageController::class, 'destroy'])
                ->name('job-stages.destroy');

            // Job Types
            Route::get('job-types', [\App\Http\Controllers\Core\Work\JobTypeController::class, 'index'])
                ->name('job-types.index');
            Route::get('job-types/create', [\App\Http\Controllers\Core\Work\JobTypeController::class, 'create'])
                ->name('job-types.create');
            Route::post('job-types', [\App\Http\Controllers\Core\Work\JobTypeController::class, 'store'])
                ->name('job-types.store');
            Route::get('job-types/{jobType}', [\App\Http\Controllers\Core\Work\JobTypeController::class, 'show'])
                ->name('job-types.show');
            Route::get('job-types/{jobType}/edit', [\App\Http\Controllers\Core\Work\JobTypeController::class, 'edit'])
                ->name('job-types.edit');
            Route::put('job-types/{jobType}', [\App\Http\Controllers\Core\Work\JobTypeController::class, 'update'])
                ->name('job-types.update');
            Route::delete('job-types/{jobType}', [\App\Http\Controllers\Core\Work\JobTypeController::class, 'destroy'])
                ->name('job-types.destroy');

            // Job Templates
            Route::get('job-templates', [\App\Http\Controllers\Core\Work\JobTemplateController::class, 'index'])
                ->name('job-templates.index');
            Route::get('job-templates/create', [\App\Http\Controllers\Core\Work\JobTemplateController::class, 'create'])
                ->name('job-templates.create');
            Route::post('job-templates', [\App\Http\Controllers\Core\Work\JobTemplateController::class, 'store'])
                ->name('job-templates.store');
            Route::get('job-templates/{jobTemplate}', [\App\Http\Controllers\Core\Work\JobTemplateController::class, 'show'])
                ->name('job-templates.show');
            Route::get('job-templates/{jobTemplate}/edit', [\App\Http\Controllers\Core\Work\JobTemplateController::class, 'edit'])
                ->name('job-templates.edit');
            Route::put('job-templates/{jobTemplate}', [\App\Http\Controllers\Core\Work\JobTemplateController::class, 'update'])
                ->name('job-templates.update');
            Route::delete('job-templates/{jobTemplate}', [\App\Http\Controllers\Core\Work\JobTemplateController::class, 'destroy'])
                ->name('job-templates.destroy');

            // Job Template Activities (module 4 — activity definitions on templates)
            Route::post('job-templates/{jobTemplate}/activities', [\App\Http\Controllers\Core\Work\JobActivityController::class, 'storeForTemplate'])
                ->name('job-template-activities.store');
            Route::delete('job-templates/{jobTemplate}/activities/{jobActivity}', [\App\Http\Controllers\Core\Work\JobActivityController::class, 'destroyTemplateActivity'])
                ->name('job-template-activities.destroy');

            // Job Activities (module 4 — live activities on service jobs)
            Route::post('service-jobs/{job}/activities', [\App\Http\Controllers\Core\Work\JobActivityController::class, 'store'])
                ->name('job-activities.store');
            Route::post('service-jobs/{job}/activities/reorder', [\App\Http\Controllers\Core\Work\JobActivityController::class, 'reorder'])
                ->name('job-activities.reorder');
            Route::post('service-jobs/{job}/activities/{jobActivity}/complete', [\App\Http\Controllers\Core\Work\JobActivityController::class, 'complete'])
                ->name('job-activities.complete');
            Route::post('service-jobs/{job}/activities/{jobActivity}/dismiss', [\App\Http\Controllers\Core\Work\JobActivityController::class, 'dismiss'])
                ->name('job-activities.dismiss');
            Route::post('service-jobs/{job}/activities/{jobActivity}/follow-up', [\App\Http\Controllers\Core\Work\JobActivityController::class, 'scheduleFollowUp'])
                ->name('job-activities.follow-up');
            Route::put('service-jobs/{job}/activities/{jobActivity}', [\App\Http\Controllers\Core\Work\JobActivityController::class, 'update'])
                ->name('job-activities.update');
            Route::delete('service-jobs/{job}/activities/{jobActivity}', [\App\Http\Controllers\Core\Work\JobActivityController::class, 'destroy'])
                ->name('job-activities.destroy');

            // ── Module 23: fieldservice_kanban_info API ────────────────────────
            Route::get('service-jobs/{job}/kanban-state', [\App\Http\Controllers\Core\Work\KanbanStatusController::class, 'show'])
                ->name('kanban.show');
            Route::post('service-jobs/{job}/kanban-state/refresh', [\App\Http\Controllers\Core\Work\KanbanStatusController::class, 'refresh'])
                ->name('kanban.refresh');
            Route::post('service-jobs/{job}/blockers', [\App\Http\Controllers\Core\Work\KanbanStatusController::class, 'addBlocker'])
                ->name('kanban.blockers.add');
            Route::delete('service-jobs/{job}/blockers/{blocker}', [\App\Http\Controllers\Core\Work\KanbanStatusController::class, 'clearBlocker'])
                ->name('kanban.blockers.clear');
            Route::get('service-jobs/{job}/dispatch-priority', [\App\Http\Controllers\Core\Work\KanbanStatusController::class, 'dispatchPriority'])
                ->name('kanban.dispatch-priority');

            // ── Modules 24+25: fieldservice_vehicle + fieldservice_vehicle_stock ─
            Route::get('vehicles', [\App\Http\Controllers\Core\Work\VehicleController::class, 'index'])
                ->name('vehicles.index');
            Route::get('vehicles/create', [\App\Http\Controllers\Core\Work\VehicleController::class, 'create'])
                ->name('vehicles.create');
            Route::post('vehicles', [\App\Http\Controllers\Core\Work\VehicleController::class, 'store'])
                ->name('vehicles.store');
            Route::get('vehicles/{vehicle}', [\App\Http\Controllers\Core\Work\VehicleController::class, 'show'])
                ->name('vehicles.show');
            Route::get('vehicles/{vehicle}/edit', [\App\Http\Controllers\Core\Work\VehicleController::class, 'edit'])
                ->name('vehicles.edit');
            Route::put('vehicles/{vehicle}', [\App\Http\Controllers\Core\Work\VehicleController::class, 'update'])
                ->name('vehicles.update');
            Route::post('vehicles/{vehicle}/assign-job', [\App\Http\Controllers\Core\Work\VehicleController::class, 'assignJob'])
                ->name('vehicles.assign-job');
            Route::post('vehicles/{vehicle}/location-snapshot', [\App\Http\Controllers\Core\Work\VehicleController::class, 'recordLocation'])
                ->name('vehicles.location-snapshot');
            Route::get('vehicles/{vehicle}/compatibility/{job}', [\App\Http\Controllers\Core\Work\VehicleController::class, 'checkCompatibility'])
                ->name('vehicles.compatibility');

            // ── MODULE_01 — TitanDispatch ──────────────────────────────────────
            Route::prefix('dispatch')->as('dispatch.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Work\DispatchController::class, 'index'])->name('index');
                Route::post('/assign', [\App\Http\Controllers\Work\DispatchController::class, 'assign'])->name('assign');
                Route::post('/auto', [\App\Http\Controllers\Work\DispatchController::class, 'autoDispatch'])->name('auto');
                Route::get('/history', [\App\Http\Controllers\Work\DispatchController::class, 'history'])->name('history');
            });

            // ── MODULE_04 — TitanContracts ─────────────────────────────────────
            Route::prefix('contracts')->as('contracts.')->group(function () {
                Route::get('renewal-queue', [\App\Http\Controllers\Work\ContractController::class, 'renewalQueue'])
                    ->name('renewal-queue');
                Route::get('{agreement}/entitlements', [\App\Http\Controllers\Work\ContractController::class, 'entitlements'])
                    ->name('entitlements');
                Route::get('{agreement}/sla-status', [\App\Http\Controllers\Work\ContractController::class, 'slaStatus'])
                    ->name('sla-status');
                Route::get('{agreement}/health', [\App\Http\Controllers\Work\ContractController::class, 'health'])
                    ->name('health');
                Route::post('{agreement}/renew', [\App\Http\Controllers\Work\ContractController::class, 'renew'])
                    ->name('renew');
            });
        });
    });
