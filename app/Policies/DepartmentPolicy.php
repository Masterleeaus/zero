<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Work\Department;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Department $department): bool
    {
        return $department->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Department $department): bool
    {
        return $user->isAdmin()
            && $department->company_id === $user->company_id;
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->isAdmin()
            && $department->company_id === $user->company_id;
    }
}
