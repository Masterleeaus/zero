<?php

use Illuminate\Support\Facades\Route;
use Modules\Timesheet\Http\Controllers\TimesheetApiController;
use Modules\Timesheet\Http\Controllers\TimesheetReportsApiController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('timesheets', [TimesheetApiController::class, 'index']);

    // Reports
    Route::get('timesheets/reports/summary', [TimesheetReportsApiController::class, 'summary']);
    Route::get('timesheets/reports/by-project', [TimesheetReportsApiController::class, 'byProject']);
    Route::get('timesheets/reports/by-user', [TimesheetReportsApiController::class, 'byUser']);
    Route::get('timesheets/reports/by-work-order', [TimesheetReportsApiController::class, 'byWorkOrder']);
});
