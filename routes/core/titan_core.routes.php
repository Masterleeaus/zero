<?php

use App\Http\Controllers\TitanCore\TitanCoreStatusController;
use App\Titan\Core\Mcp\Tools\MemoryRecallTool;
use App\Titan\Core\Mcp\Tools\MemoryStoreTool;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity', 'throttle:120,1'])
    ->prefix('dashboard/user/business-suite/core')
    ->as('titan.core.')
    ->group(static function () {
        Route::get('/', [TitanCoreStatusController::class, 'index'])->name('status');
        Route::get('/health', [TitanCoreStatusController::class, 'api'])->name('health');
        Route::get('/runtime', [TitanCoreStatusController::class, 'runtime'])->name('runtime');
    });

Route::middleware(['auth:sanctum', 'throttle:60,1'])
    ->prefix('api/titan/memory')
    ->as('titan.memory.')
    ->group(static function () {
        Route::post('/recall', [MemoryRecallTool::class, 'respond'])->name('recall');
        Route::post('/store', [MemoryStoreTool::class, 'respond'])->name('store');
    });
