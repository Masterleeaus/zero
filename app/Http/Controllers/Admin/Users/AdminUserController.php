<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminAuditService;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AdminUserController
 *
 * CRUD management of users within the Titan Admin panel.
 * Tenant-aware: all operations are scoped through AdminUserService.
 *
 * Routes: titan.admin.users.*
 */
class AdminUserController extends Controller
{
    public function __construct(
        protected AdminUserService $userService,
        protected AdminAuditService $auditService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'role']);
        $users   = $this->userService->paginate($filters);
        $roles   = $this->userService->allRoles();

        return view('panel.admin.users.index', compact('users', 'roles', 'filters'));
    }

    public function create(): View
    {
        $roles = $this->userService->allRoles();

        return view('panel.admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'nullable|string|exists:roles,name',
        ]);

        $this->userService->create($validated);

        return redirect()->route('titan.admin.users.index')
            ->with('success', __('User created successfully.'));
    }

    public function edit(User $user): View
    {
        $roles       = $this->userService->allRoles();
        $currentRole = $user->roles->first()?->name;

        return view('panel.admin.users.edit', compact('user', 'roles', 'currentRole'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'nullable|string|exists:roles,name',
        ]);

        $this->userService->update($user, $validated);

        return redirect()->route('titan.admin.users.index')
            ->with('success', __('User updated successfully.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->userService->delete($user);

        return redirect()->route('titan.admin.users.index')
            ->with('success', __('User removed successfully.'));
    }
}
