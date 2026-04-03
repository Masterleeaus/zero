<?php

use Illuminate\Support\Facades\Route;
use Modules\Timesheet\Http\Controllers\TimesheetController;
use Modules\Timesheet\Http\Controllers\TimesheetExportController;
use Modules\Timesheet\Http\Controllers\TimesheetTimerController;
use Modules\Timesheet\Http\Controllers\TimesheetApprovalController;
use Modules\Timesheet\Http\Controllers\TimesheetSettingsController;
use Modules\Timesheet\Http\Controllers\TimesheetReportsController;

Route::group([
    'middleware' => ['web', 'auth', 'PlanModuleCheck:Timesheet'],
    'prefix' => 'account/timesheets',
    'as' => 'timesheet.',
], function () {
    Route::get('/', [TimesheetController::class, 'index'])->name('index');
    Route::get('/create', [TimesheetController::class, 'create'])->name('create');
    Route::post('/', [TimesheetController::class, 'store'])->name('store');
    Route::get('/{timesheet}/edit', [TimesheetController::class, 'edit'])->name('edit');
    Route::put('/{timesheet}', [TimesheetController::class, 'update'])->name('update');
    Route::delete('/{timesheet}', [TimesheetController::class, 'destroy'])->name('destroy');

    // Helpers (auth + permission guarded inside controller)
    Route::get('/stats/total-hours', [TimesheetController::class,'totalhours'])->name('stats.totalhours');
    Route::get('/stats/hours/{user}', [TimesheetController::class,'gethours'])->name('stats.gethours');
    Route::post('/lookups/tasks', [TimesheetController::class,'gettask'])->name('lookups.tasks');
    Route::post('/lookups/work-orders', [TimesheetController::class,'getWorkOrders'])->name('lookups.work_orders');



    // Timer (clock on/off)
    Route::get('/timer', [TimesheetTimerController::class, 'index'])->name('timer.index');
    Route::post('/timer/start', [TimesheetTimerController::class, 'start'])->name('timer.start');
    Route::post('/timer/stop', [TimesheetTimerController::class, 'stop'])->name('timer.stop');

    // Weekly approvals
    Route::get('/approvals/my-week', [TimesheetApprovalController::class, 'myWeek'])->name('approvals.my_week');
    Route::post('/approvals/submit', [TimesheetApprovalController::class, 'submit'])->name('approvals.submit');
    Route::get('/approvals/inbox', [TimesheetApprovalController::class, 'inbox'])->name('approvals.inbox');
    Route::get('/approvals/{submission}', [TimesheetApprovalController::class, 'show'])->name('approvals.show');
    Route::post('/approvals/{submission}/approve', [TimesheetApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{submission}/reject', [TimesheetApprovalController::class, 'reject'])->name('approvals.reject');


    // Reports
    Route::get('/reports', [TimesheetReportsController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/reports/crew', [TimesheetReportsController::class, 'crew'])->name('reports.crew');
    Route::get('/reports/projects', [TimesheetReportsController::class, 'projects'])->name('reports.projects');
    Route::get('/reports/work-orders', [TimesheetReportsController::class, 'workOrders'])->name('reports.work_orders');

    // Settings (tenant)
    Route::get('/settings', [TimesheetSettingsController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [TimesheetSettingsController::class, 'update'])->name('settings.update');

    // Export
    Route::get('/export/csv', [TimesheetExportController::class, 'csv'])->name('export.csv');
});
