<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin\AdminAuditLog;
use App\Services\Admin\AdminAuditService;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AdminSettingsService;
use App\Services\Admin\AdminUserService;
use App\Support\Admin\AdminHelpers;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * AdminModuleTest
 *
 * Validates that the Titan Admin module routes are registered,
 * services are bound, the model is configured correctly, and
 * helper utilities function as expected.
 */
class AdminModuleTest extends TestCase
{
    // ─── Route Registration ────────────────────────────────────────────────

    public function test_admin_user_routes_are_registered(): void
    {
        $routes = [
            'titan.admin.users.index',
            'titan.admin.users.create',
            'titan.admin.users.store',
            'titan.admin.users.edit',
            'titan.admin.users.update',
            'titan.admin.users.destroy',
        ];

        foreach ($routes as $name) {
            $this->assertTrue(Route::has($name), "Route [{$name}] should be registered");
        }
    }

    public function test_admin_role_routes_are_registered(): void
    {
        $routes = [
            'titan.admin.roles.index',
            'titan.admin.roles.store',
            'titan.admin.roles.update',
            'titan.admin.roles.destroy',
        ];

        foreach ($routes as $name) {
            $this->assertTrue(Route::has($name), "Route [{$name}] should be registered");
        }
    }

    public function test_admin_settings_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('titan.admin.settings.index'));
        $this->assertTrue(Route::has('titan.admin.settings.update'));
    }

    public function test_admin_audit_route_is_registered(): void
    {
        $this->assertTrue(Route::has('titan.admin.audit.index'));
    }

    // ─── Service Provider Bindings ─────────────────────────────────────────

    public function test_admin_services_are_bound(): void
    {
        $this->assertInstanceOf(AdminUserService::class, app(AdminUserService::class));
        $this->assertInstanceOf(AdminRoleService::class, app(AdminRoleService::class));
        $this->assertInstanceOf(AdminSettingsService::class, app(AdminSettingsService::class));
        $this->assertInstanceOf(AdminAuditService::class, app(AdminAuditService::class));
    }

    public function test_admin_config_is_merged(): void
    {
        $this->assertIsArray(config('admin'));
        $this->assertArrayHasKey('audit', config('admin'));
        $this->assertArrayHasKey('users', config('admin'));
    }

    // ─── Model ────────────────────────────────────────────────────────────

    public function test_admin_audit_log_uses_correct_table(): void
    {
        $model = new AdminAuditLog;
        $this->assertSame('tz_audit_log', $model->getTable());
    }

    public function test_admin_audit_log_casts_details_as_array(): void
    {
        $model = new AdminAuditLog(['details' => ['key' => 'value']]);
        $this->assertIsArray($model->details);
    }

    // ─── Helper ───────────────────────────────────────────────────────────

    public function test_admin_helpers_permission_label(): void
    {
        $label = AdminHelpers::permissionLabel('admin_users_edit');
        $this->assertSame('Admin Users Edit', $label);
    }

    public function test_admin_helpers_prefix(): void
    {
        $this->assertSame('/dashboard/admin', AdminHelpers::adminPrefix());
    }

    public function test_admin_helpers_returns_false_for_unauthenticated(): void
    {
        $this->assertFalse(AdminHelpers::isAdminUser(null));
    }
}
