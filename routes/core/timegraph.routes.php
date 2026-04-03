<?php

use App\Http\Controllers\TimeGraph\ExecutionTimeGraphController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:' . config('throttle.dashboard', '120,1')])
    ->prefix('dashboard/timegraph')
    ->as('dashboard.timegraph.')
    ->group(function () {
        Route::get('{graphId}/timeline', [ExecutionTimeGraphController::class, 'timeline'])->name('timeline');
        Route::get('{graphId}', [ExecutionTimeGraphController::class, 'graph'])->name('graph');
        Route::post('{graphId}/checkpoint', [ExecutionTimeGraphController::class, 'checkpoint'])->name('checkpoint');
        Route::post('{graphId}/replay', [ExecutionTimeGraphController::class, 'replay'])->name('replay');
        Route::get('{graphId}/describe', [ExecutionTimeGraphController::class, 'describe'])->name('describe');
    });
