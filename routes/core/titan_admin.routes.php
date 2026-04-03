<?php

use App\Http\Controllers\Admin\TitanCore\TitanCoreAdminController;
use Illuminate\Support\Facades\Route;

/**
 * Titan Core Admin Panel Routes
 *
 * Mounted at: /dashboard/admin/titan/core
 * Middleware: auth + admin + updateUserActivity
 * Name prefix: admin.titan.core.
 */
Route::middleware(['auth', 'admin', 'updateUserActivity'])
    ->prefix('dashboard/admin/titan/core')
    ->as('admin.titan.core.')
    ->group(static function () {

        // Phase 5.2 – Model Routing
        Route::get('/models', [TitanCoreAdminController::class, 'models'])->name('models');
        Route::post('/models', [TitanCoreAdminController::class, 'modelsUpdate'])->name('models.update');

        // Phase 5.3 – Signal Queue Monitor
        Route::get('/signals', [TitanCoreAdminController::class, 'signals'])->name('signals');

        // Phase 5.4 – Memory Usage Panel
        Route::get('/memory', [TitanCoreAdminController::class, 'memory'])->name('memory');
        Route::post('/memory/purge', [TitanCoreAdminController::class, 'memoryPurge'])->name('memory.purge');
        Route::post('/memory/summarise', [TitanCoreAdminController::class, 'memorySummarise'])->name('memory.summarise');

        // Phase 5.5 – Skill Runtime Monitor
        Route::get('/skills', [TitanCoreAdminController::class, 'skills'])->name('skills');
        Route::post('/skills/restart', [TitanCoreAdminController::class, 'skillRestart'])->name('skills.restart');
        Route::post('/skills/disable', [TitanCoreAdminController::class, 'skillDisable'])->name('skills.disable');

        // Phase 5.6 – Activity Feed
        Route::get('/activity', [TitanCoreAdminController::class, 'activity'])->name('activity');

        // Phase 5.7 – Token Budgets
        Route::get('/budgets', [TitanCoreAdminController::class, 'budgets'])->name('budgets');
        Route::post('/budgets', [TitanCoreAdminController::class, 'budgetsUpdate'])->name('budgets.update');

        // Phase 5.8 – Queue Dashboard
        Route::get('/queues', [TitanCoreAdminController::class, 'queues'])->name('queues');
        Route::post('/queues/retry', [TitanCoreAdminController::class, 'queueRetryFailed'])->name('queues.retry');
        Route::post('/queues/flush', [TitanCoreAdminController::class, 'queueFlush'])->name('queues.flush');

        // Phase 5.9 / 5.14 – Health Dashboard
        Route::get('/health', [TitanCoreAdminController::class, 'health'])->name('health');
        Route::get('/health/api', [TitanCoreAdminController::class, 'healthApi'])->name('health.api');
    });
