<?php

declare(strict_types=1);

use App\Http\Controllers\Core\Security\BlacklistEmailController;
use App\Http\Controllers\Core\Security\BlacklistIpController;
use App\Http\Controllers\Core\Security\SecuritySettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Security Domain Routes
|--------------------------------------------------------------------------
|
| These routes are loaded inside the 'auth' + 'throttle:120,1' group by
| RouteServiceProvider. All endpoints are admin-only and enforce the
| security.blacklist_ip and security.blacklist_email middleware.
|
*/

Route::middleware(['auth', 'throttle:120,1', 'security.blacklist_ip'])
    ->prefix('dashboard/security')
    ->as('dashboard.security.')
    ->group(static function () {

        // ── Settings ─────────────────────────────────────────────────────
        Route::get('settings', [SecuritySettingsController::class, 'index'])
            ->name('settings.index');

        Route::put('settings/login-protection', [SecuritySettingsController::class, 'updateLoginProtection'])
            ->name('settings.login-protection');

        Route::put('settings/session-policy', [SecuritySettingsController::class, 'updateSessionPolicy'])
            ->name('settings.session-policy');

        // ── Audit trail ───────────────────────────────────────────────────
        Route::get('audit', [SecuritySettingsController::class, 'auditTrail'])
            ->name('audit.index');

        // ── IP Blacklist ──────────────────────────────────────────────────
        Route::get('blacklist/ips', [BlacklistIpController::class, 'index'])
            ->name('blacklist.ips.index');

        Route::post('blacklist/ips', [BlacklistIpController::class, 'store'])
            ->name('blacklist.ips.store');

        Route::put('blacklist/ips/{blacklistIp}', [BlacklistIpController::class, 'update'])
            ->name('blacklist.ips.update');

        Route::delete('blacklist/ips/{blacklistIp}', [BlacklistIpController::class, 'destroy'])
            ->name('blacklist.ips.destroy');

        // ── Email Blacklist ───────────────────────────────────────────────
        Route::get('blacklist/emails', [BlacklistEmailController::class, 'index'])
            ->name('blacklist.emails.index');

        Route::post('blacklist/emails', [BlacklistEmailController::class, 'store'])
            ->name('blacklist.emails.store');

        Route::put('blacklist/emails/{blacklistEmail}', [BlacklistEmailController::class, 'update'])
            ->name('blacklist.emails.update');

        Route::delete('blacklist/emails/{blacklistEmail}', [BlacklistEmailController::class, 'destroy'])
            ->name('blacklist.emails.destroy');
    });
