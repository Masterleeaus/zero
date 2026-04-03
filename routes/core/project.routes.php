<?php

use App\Http\Controllers\Core\Work\FieldServiceProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/work/projects')->name('work.projects.')->group(function () {
    Route::get('/', [FieldServiceProjectController::class, 'index'])->name('index');
    Route::post('/', [FieldServiceProjectController::class, 'store'])->name('store');
    Route::get('/{id}', [FieldServiceProjectController::class, 'show'])->name('show');
    Route::put('/{id}', [FieldServiceProjectController::class, 'update'])->name('update');
    Route::post('/{id}/link-job', [FieldServiceProjectController::class, 'linkJob'])->name('link-job');
});
