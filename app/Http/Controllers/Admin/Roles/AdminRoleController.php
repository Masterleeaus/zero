<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Roles;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * AdminRoleController
 *
 * Manages Spatie roles and permissions from the Titan Admin panel.
 * Core roles (user, admin, super_admin) are protected from deletion.
 *
 * Routes: titan.admin.roles.*
 */
class AdminRoleController extends Controller
{
    public function __construct(
        protected AdminRoleService $roleService,
    ) {
    }

    public function index(): View
    {
        $roles       = $this->roleService->allRoles();
        $permissions = $this->roleService->allPermissions();

        return view('panel.admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $this->roleService->createRole($validated['name'], $validated['permissions'] ?? []);

        return redirect()->route('titan.admin.roles.index')
            ->with('success', __('Role created successfully.'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $this->roleService->syncPermissions($role, $validated['permissions'] ?? []);

        return redirect()->route('titan.admin.roles.index')
            ->with('success', __('Role permissions updated.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $deleted = $this->roleService->deleteRole($role);

        if (! $deleted) {
            return redirect()->route('titan.admin.roles.index')
                ->with('error', __('Core roles cannot be deleted.'));
        }

        return redirect()->route('titan.admin.roles.index')
            ->with('success', __('Role deleted.'));
    }
}
