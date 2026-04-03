<?php

use App\Http\Controllers\TitanSignalApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/signals')->middleware(['api'])->group(function () {
    Route::post('ingest', [TitanSignalApiController::class, 'ingest'])->name('api.signals.ingest');
    Route::post('publish', [TitanSignalApiController::class, 'publish'])->name('api.signals.publish');
    Route::get('registry', [TitanSignalApiController::class, 'registry'])->name('api.signals.registry');
    Route::post('processes/record', [TitanSignalApiController::class, 'recordProcess'])->name('api.signals.processes.record');
    Route::post('processes/record-and-ingest', [TitanSignalApiController::class, 'recordAndIngest'])->name('api.signals.processes.record_and_ingest');
    Route::get('processes/{processId}', [TitanSignalApiController::class, 'process'])->name('api.signals.processes.show');
    Route::post('processes/{processId}/transition', [TitanSignalApiController::class, 'transitionProcess'])->name('api.signals.processes.transition');
    Route::post('dispatch/pending', [TitanSignalApiController::class, 'dispatchPending'])->name('api.signals.dispatch.pending');
    Route::get('approvals', [TitanSignalApiController::class, 'approvals'])->name('api.signals.approvals');
    Route::post('approvals/{processId}', [TitanSignalApiController::class, 'approve'])->name('api.signals.approvals.decide');
    Route::get('feed', [TitanSignalApiController::class, 'feed'])->name('api.signals.feed');
    Route::get('timeline/{processId}', [TitanSignalApiController::class, 'timeline'])->name('api.signals.timeline');
    Route::post('envelope', [TitanSignalApiController::class, 'envelope'])->name('api.signals.envelope');
});
