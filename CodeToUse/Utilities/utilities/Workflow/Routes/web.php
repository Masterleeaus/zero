<?php

use Illuminate\Support\Facades\Route;
use Modules\Workflow\Http\Controllers\Account\WorkflowsController as AccountWorkflowsController;
use Modules\Workflow\Http\Controllers\Account\WorkflowRunsController as AccountWorkflowRunsController;
use Modules\Workflow\Http\Controllers\Admin\WorkflowsController as AdminWorkflowsController;
use Modules\Workflow\Http\Controllers\Admin\WorkflowRunsController as AdminWorkflowRunsController;
use Modules\Workflow\Http\Controllers\Admin\WorkflowDiagnosticsController as AdminWorkflowDiagnosticsController;

/*
|--------------------------------------------------------------------------
| Workflow Routes
|--------------------------------------------------------------------------
| Worksuite routing law:
| - Tenant UI routes must be under `account/*` with `company` middleware
| - Superadmin/system routes must be under `admin/*` with `superadmin` middleware
| - Never reuse controllers between contexts
*/

// Tenant (company/account) context — operates on tenant data
Route::middleware(['web', 'auth', 'company'])
    ->prefix('account/settings/workflow')
    ->name('workflow.account.workflows.')
    ->group(function () {
        Route::get('/', [AccountWorkflowsController::class, 'index'])
            ->name('index')
            ->middleware('permission:view_workflow');

        Route::get('/create', [AccountWorkflowsController::class, 'create'])
            ->name('create')
            ->middleware('permission:manage_workflow');

        Route::post('/', [AccountWorkflowsController::class, 'store'])
            ->name('store')
            ->middleware('permission:manage_workflow');

        Route::get('/{id}/edit', [AccountWorkflowsController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:manage_workflow');

        Route::put('/{id}', [AccountWorkflowsController::class, 'update'])
            ->name('update')
            ->middleware('permission:manage_workflow');

        Route::get('/{id}/timeline', [AccountWorkflowsController::class, 'timeline'])
            ->name('timeline')
            ->middleware('permission:view_workflow');

        Route::post('/{id}/run', [AccountWorkflowsController::class, 'run'])
            ->name('run')
            ->middleware('permission:manage_workflow');

        // Runs (tenant-scoped)
        Route::get('/runs', [AccountWorkflowRunsController::class, 'index'])
            ->name('runs.index')
            ->middleware('permission:view_workflow');

        Route::get('/runs/{runId}', [AccountWorkflowRunsController::class, 'show'])
            ->name('runs.show')
            ->middleware('permission:view_workflow');
    });

// Superadmin / system context — configuration / diagnostics
Route::middleware(['web', 'auth', 'superadmin'])
    ->prefix('admin/settings/workflow')
    ->name('workflow.admin.workflows.')
    ->group(function () {
        Route::get('/', [AdminWorkflowsController::class, 'index'])
            ->name('index')
            ->middleware('permission:view_workflow');

        Route::get('/create', [AdminWorkflowsController::class, 'create'])
            ->name('create')
            ->middleware('permission:manage_workflow');

        Route::post('/', [AdminWorkflowsController::class, 'store'])
            ->name('store')
            ->middleware('permission:manage_workflow');

        Route::get('/{id}/edit', [AdminWorkflowsController::class, 'edit'])
            ->name('edit')
            ->middleware('permission:manage_workflow');

        Route::put('/{id}', [AdminWorkflowsController::class, 'update'])
            ->name('update')
            ->middleware('permission:manage_workflow');

        Route::get('/{id}/timeline', [AdminWorkflowsController::class, 'timeline'])
            ->name('timeline')
            ->middleware('permission:view_workflow');

        Route::post('/{id}/run', [AdminWorkflowsController::class, 'run'])
            ->name('run')
            ->middleware('permission:manage_workflow');

        // Runs (system view)
        Route::get('/runs', [AdminWorkflowRunsController::class, 'index'])
            ->name('runs.index')
            ->middleware('permission:view_workflow');

        Route::get('/runs/{runId}', [AdminWorkflowRunsController::class, 'show'])
            ->name('runs.show')
            ->middleware('permission:view_workflow');

        // Diagnostics
        Route::get('/diagnostics', [AdminWorkflowDiagnosticsController::class, 'index'])
            ->name('diagnostics.index')
            ->middleware('permission:manage_workflow');
    });
