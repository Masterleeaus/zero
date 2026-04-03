<?php

declare(strict_types=1);

namespace App\Services\Admin;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * AdminRoleService
 *
 * Manages roles and permissions for the Titan Admin panel.
 * Wraps Spatie Permission package operations.
 */
class AdminRoleService
{
    /**
     * Return all roles with their permissions eagerly loaded.
     *
     * @return Collection<int, Role>
     */
    public function allRoles(): Collection
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    /**
     * Return all registered permissions ordered by name.
     *
     * @return Collection<int, Permission>
     */
    public function allPermissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }

    /**
     * Create a new role and optionally assign permissions.
     *
     * @param  string[]  $permissions
     */
    public function createRole(string $name, array $permissions = []): Role
    {
        $role = Role::create(['name' => $name, 'guard_name' => 'web']);

        if (! empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    /**
     * Sync the permission set for an existing role.
     *
     * @param  string[]  $permissions
     */
    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }

    /**
     * Delete a role by name if it is not a core Titan role.
     *
     * Core roles (user, admin, super_admin) are protected.
     */
    public function deleteRole(Role $role): bool
    {
        $protected = ['user', 'admin', 'super_admin'];

        if (in_array($role->name, $protected, true)) {
            return false;
        }

        $role->delete();

        return true;
    }
}
