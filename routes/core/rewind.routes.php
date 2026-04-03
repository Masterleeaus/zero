<?php

use App\Extensions\TitanRewind\System\Http\Controllers\TitanRewindCaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:120,1'])
    ->prefix('dashboard/user/titanrewind')
    ->name('titanrewind.')
    ->group(function () {
        Route::get('/', [TitanRewindCaseController::class, 'index'])->name('cases.index');
        Route::get('/manual-review', [TitanRewindCaseController::class, 'manualReview'])->name('cases.manualReview');
        Route::post('/initiate', [TitanRewindCaseController::class, 'initiate'])->name('cases.initiate');
        Route::get('/cases/{case}', [TitanRewindCaseController::class, 'show'])->name('cases.show');
        Route::get('/cases/{case}/timeline', [TitanRewindCaseController::class, 'timeline'])->name('cases.timeline');
        Route::get('/cases/{case}/plan', [TitanRewindCaseController::class, 'plan'])->name('cases.plan');
        Route::get('/cases/{case}/replay', [TitanRewindCaseController::class, 'replay'])->name('cases.replay');
        Route::post('/cases/{case}/promote-lifecycle', [TitanRewindCaseController::class, 'promoteLifecycle'])->name('cases.promoteLifecycle');
        Route::post('/cases/{case}/submit-correction', [TitanRewindCaseController::class, 'submitCorrection'])->name('cases.submitCorrection');
        Route::post('/cases/{case}/complete-rollback', [TitanRewindCaseController::class, 'completeRollback'])->name('cases.completeRollback');
        Route::post('/cases/{case}/conflicts/{conflict}/resolve', [TitanRewindCaseController::class, 'resolveConflict'])->name('cases.conflicts.resolve');
        Route::post('/cases/{case}/propose-fix', [TitanRewindCaseController::class, 'proposeFix'])->name('cases.proposeFix');
        Route::post('/cases/{case}/apply-fix', [TitanRewindCaseController::class, 'applyFix'])->name('cases.applyFix');
        Route::post('/cases/{case}/resolve', [TitanRewindCaseController::class, 'resolve'])->name('cases.resolve');
    });
