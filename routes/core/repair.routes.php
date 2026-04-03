<?php

declare(strict_types=1);

use App\Http\Controllers\Core\Repair\RepairOrderController;
use App\Http\Controllers\Core\Repair\RepairTemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Repair Domain Routes — FSM Modules 9 + 10
|--------------------------------------------------------------------------
|
| Loaded automatically by RouteServiceProvider::loadCoreRoutes()
| All routes inherit: web, auth, throttle:120,1
|
| Prefix: /repair
|
*/

Route::prefix('repair')->name('repair.')->group(function () {

    // ── Repair Orders (Module 9) ──────────────────────────────────────────────
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [RepairOrderController::class, 'index'])->name('index');
        Route::get('/create', [RepairOrderController::class, 'create'])->name('create');
        Route::post('/', [RepairOrderController::class, 'store'])->name('store');
        Route::get('/{repair}', [RepairOrderController::class, 'show'])->name('show');
        Route::get('/{repair}/edit', [RepairOrderController::class, 'edit'])->name('edit');
        Route::put('/{repair}', [RepairOrderController::class, 'update'])->name('update');

        // Diagnosis
        Route::post('/{repair}/diagnoses', [RepairOrderController::class, 'storeDiagnosis'])
            ->name('diagnoses.store');

        // Template application
        Route::post('/{repair}/apply-template', [RepairOrderController::class, 'applyTemplate'])
            ->name('apply_template');

        // Lifecycle actions
        Route::post('/{repair}/complete', [RepairOrderController::class, 'complete'])
            ->name('complete');
    });

    // ── Repair Templates (Module 10) ──────────────────────────────────────────
    Route::resource('templates', RepairTemplateController::class)
        ->names([
            'index'   => 'templates.index',
            'create'  => 'templates.create',
            'store'   => 'templates.store',
            'show'    => 'templates.show',
            'edit'    => 'templates.edit',
            'update'  => 'templates.update',
            'destroy' => 'templates.destroy',
        ]);
});
