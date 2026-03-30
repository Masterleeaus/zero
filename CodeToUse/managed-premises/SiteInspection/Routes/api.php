<?php

use Illuminate\Support\Facades\Route;
use Modules\Inspection\Http\Controllers\ScheduleController;
use Modules\Inspection\Http\Controllers\RecurringScheduleController;
use Modules\Inspection\Http\Controllers\ScheduleReplyController;
use Modules\Inspection\Http\Controllers\ScheduleFileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Exposes JSON endpoints for the Inspection module. These are stateless and
| guarded by API auth. Adjust middleware to match your host app (Sanctum, Passport).
*/

Route::middleware(['api','auth:sanctum'])->prefix('inspection')->group(function () {
    // Schedules
    Route::get('schedules', [ScheduleController::class, 'index']);
    Route::get('schedules/{schedule}', [ScheduleController::class, 'show']);
    Route::post('schedules', [ScheduleController::class, 'store']);
    Route::put('schedules/{schedule}', [ScheduleController::class, 'update']);
    Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy']);

    // Recurring Schedules
    Route::get('recurring-schedules', [RecurringScheduleController::class, 'index']);
    Route::get('recurring-schedules/{recurringSchedule}', [RecurringScheduleController::class, 'show']);
    Route::post('recurring-schedules', [RecurringScheduleController::class, 'store']);
    Route::put('recurring-schedules/{recurringSchedule}', [RecurringScheduleController::class, 'update']);
    Route::delete('recurring-schedules/{recurringSchedule}', [RecurringScheduleController::class, 'destroy']);

    // Files
    Route::get('files', [ScheduleFileController::class, 'index']);
    Route::get('files/{file}', [ScheduleFileController::class, 'show']);
    Route::post('files', [ScheduleFileController::class, 'store']);
    Route::delete('files/{file}', [ScheduleFileController::class, 'destroy']);

    // Replies
    Route::get('replies', [ScheduleReplyController::class, 'index']);
    Route::get('replies/{reply}', [ScheduleReplyController::class, 'show']);
    Route::post('replies', [ScheduleReplyController::class, 'store']);
    Route::delete('replies/{reply}', [ScheduleReplyController::class, 'destroy']);
});


// Alias routes under /api/site-inspection (mirrors /api/inspection)
Route::middleware(['api','auth:sanctum'])->prefix('site-inspection')->group(function () {
// Schedules
    Route::get('schedules', [ScheduleController::class, 'index']);
    Route::get('schedules/{schedule}', [ScheduleController::class, 'show']);
    Route::post('schedules', [ScheduleController::class, 'store']);
    Route::put('schedules/{schedule}', [ScheduleController::class, 'update']);
    Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy']);

    // Recurring Schedules
    Route::get('recurring-schedules', [RecurringScheduleController::class, 'index']);
    Route::get('recurring-schedules/{recurringSchedule}', [RecurringScheduleController::class, 'show']);
    Route::post('recurring-schedules', [RecurringScheduleController::class, 'store']);
    Route::put('recurring-schedules/{recurringSchedule}', [RecurringScheduleController::class, 'update']);
    Route::delete('recurring-schedules/{recurringSchedule}', [RecurringScheduleController::class, 'destroy']);

    // Files
    Route::get('files', [ScheduleFileController::class, 'index']);
    Route::get('files/{file}', [ScheduleFileController::class, 'show']);
    Route::post('files', [ScheduleFileController::class, 'store']);
    Route::delete('files/{file}', [ScheduleFileController::class, 'destroy']);

    // Replies
    Route::get('replies', [ScheduleReplyController::class, 'index']);
    Route::get('replies/{reply}', [ScheduleReplyController::class, 'show']);
    Route::post('replies', [ScheduleReplyController::class, 'store']);
    Route::delete('replies/{reply}', [ScheduleReplyController::class, 'destroy']);
});
