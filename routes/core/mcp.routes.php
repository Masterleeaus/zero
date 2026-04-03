<?php

use App\Http\Controllers\TitanCore\MCP\McpServerController;
use App\Http\Middleware\TitanCore\EnforceTitanTenancy;
use App\Http\Middleware\TitanCore\ValidateZylosSignature;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Titan MCP Routes
|--------------------------------------------------------------------------
|
| All MCP endpoints require auth:sanctum + tenant enforcement.
| The Zylos skill-callback endpoint additionally requires a valid
| HMAC-SHA256 signature in the X-Zylos-Signature header.
|
*/

Route::middleware(['auth:sanctum', EnforceTitanTenancy::class, 'throttle:60,1'])
    ->prefix('api/titan/mcp')
    ->as('titan.mcp.')
    ->group(static function () {
        Route::get('capabilities', [McpServerController::class, 'capabilities'])->name('capabilities');
        Route::post('invoke', [McpServerController::class, 'invoke'])->name('invoke');
    });

Route::middleware([ValidateZylosSignature::class, 'throttle:120,1'])
    ->prefix('api/titan/signal')
    ->as('titan.signal.')
    ->group(static function () {
        Route::post('callback', [McpServerController::class, 'skillCallback'])->name('callback');
    });
