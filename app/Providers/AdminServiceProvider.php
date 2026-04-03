<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Admin\AdminAuditService;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AdminSettingsService;
use App\Services\Admin\AdminUserService;
use Illuminate\Support\ServiceProvider;

/**
 * AdminServiceProvider
 *
 * Registers all Titan Admin module bindings:
 *   - AdminUserService
 *   - AdminRoleService
 *   - AdminSettingsService
 *   - AdminAuditService
 *
 * Routes are loaded automatically by RouteServiceProvider scanning
 * routes/core/admin.routes.php.
 * Views resolve under the standard panel.admin.* namespace.
 */
class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdminUserService::class);
        $this->app->singleton(AdminRoleService::class);
        $this->app->singleton(AdminSettingsService::class);
        $this->app->singleton(AdminAuditService::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            base_path('config/admin.php'),
            'admin',
        );
    }
}
