<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Work\Shift;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Shift $shift): bool
    {
        return $shift->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->isAdmin()
            && $shift->company_id === $user->company_id;
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $user->isAdmin()
            && $shift->company_id === $user->company_id;
    }
}
