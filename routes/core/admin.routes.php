<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AuditLog\AdminAuditLogController;
use App\Http\Controllers\Admin\Roles\AdminRoleController;
use App\Http\Controllers\Admin\Settings\AdminSettingsController;
use App\Http\Controllers\Admin\Users\AdminUserController;
use Illuminate\Support\Facades\Route;

/**
 * Titan Admin Core Routes
 *
 * Mounted at: /dashboard/admin
 * Middleware: auth + admin + updateUserActivity
 * Name prefix: titan.admin.
 *
 * Phase 9 — First Conversion Pass:
 *   users, roles, permissions, settings, audit logs
 */
Route::middleware(['auth', 'admin', 'updateUserActivity'])
    ->prefix('dashboard/admin')
    ->as('titan.admin.')
    ->group(static function () {

        // ─── Users ────────────────────────────────────────────────────────
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // ─── Roles & Permissions ──────────────────────────────────────────
        Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');

        // ─── Settings ─────────────────────────────────────────────────────
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

        // ─── Audit Log ────────────────────────────────────────────────────
        Route::get('/audit', [AdminAuditLogController::class, 'index'])->name('audit.index');
    });
