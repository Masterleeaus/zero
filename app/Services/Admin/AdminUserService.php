<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * AdminUserService
 *
 * Handles tenant-aware user management for the Admin panel.
 * Wraps Spatie roles and standard Eloquent user operations.
 */
class AdminUserService
{
    /**
     * Paginated user list, optionally filtered by search term and role.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = User::query()->with('roles');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(static function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['role'])) {
            $query->role($filters['role']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Create a new user and assign the given role.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (! empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user;
    }

    /**
     * Update user attributes. If a new password is supplied it is hashed.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $payload = array_filter([
            'name'  => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user->fresh();
    }

    /**
     * Soft-delete or hard-delete a user depending on config.
     */
    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    /**
     * Return all Spatie roles keyed by name.
     *
     * @return \Illuminate\Support\Collection<string, Role>
     */
    public function allRoles()
    {
        return Role::orderBy('name')->get()->keyBy('name');
    }
}
