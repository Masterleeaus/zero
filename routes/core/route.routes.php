<?php

use Illuminate\Support\Facades\Route;

/**
 * FSM Module 11 — fieldservice_route
 *
 * Dispatch routes, day-route stops, and technician availability management.
 * All routes are auto-loaded by RouteServiceProvider under web + auth middleware.
 */

Route::middleware(['auth', 'updateUserActivity', 'throttle:' . config('throttle.dashboard', '120,1')])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('work')->as('work.')->group(static function () {

            // ── Dispatch Routes ────────────────────────────────────────────────
            Route::get('routes', [\App\Http\Controllers\Core\Route\DispatchRouteController::class, 'index'])
                ->name('routes.index');
            Route::get('routes/create', [\App\Http\Controllers\Core\Route\DispatchRouteController::class, 'create'])
                ->name('routes.create');
            Route::post('routes', [\App\Http\Controllers\Core\Route\DispatchRouteController::class, 'store'])
                ->name('routes.store');
            Route::get('routes/{route}', [\App\Http\Controllers\Core\Route\DispatchRouteController::class, 'show'])
                ->name('routes.show');
            Route::get('routes/{route}/edit', [\App\Http\Controllers\Core\Route\DispatchRouteController::class, 'edit'])
                ->name('routes.edit');
            Route::put('routes/{route}', [\App\Http\Controllers\Core\Route\DispatchRouteController::class, 'update'])
                ->name('routes.update');
            Route::delete('routes/{route}', [\App\Http\Controllers\Core\Route\DispatchRouteController::class, 'destroy'])
                ->name('routes.destroy');

            // ── Technician Availability ────────────────────────────────────────
            Route::prefix('routes/availability')->as('routes.availability.')->group(static function () {
                Route::get('/', [\App\Http\Controllers\Core\Route\TechnicianAvailabilityController::class, 'index'])
                    ->name('index');
                Route::get('create', [\App\Http\Controllers\Core\Route\TechnicianAvailabilityController::class, 'create'])
                    ->name('create');
                Route::post('/', [\App\Http\Controllers\Core\Route\TechnicianAvailabilityController::class, 'store'])
                    ->name('store');
                Route::get('{availability}/edit', [\App\Http\Controllers\Core\Route\TechnicianAvailabilityController::class, 'edit'])
                    ->name('edit');
                Route::put('{availability}', [\App\Http\Controllers\Core\Route\TechnicianAvailabilityController::class, 'update'])
                    ->name('update');
                Route::delete('{availability}', [\App\Http\Controllers\Core\Route\TechnicianAvailabilityController::class, 'destroy'])
                    ->name('destroy');
            });
        });
    });
