<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Work\Leave;

class LeavePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Leave $leave): bool
    {
        return $leave->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Leave $leave): bool
    {
        return $leave->company_id === $user->company_id
            && ($user->isAdmin() || $leave->user_id === $user->id);
    }

    public function approve(User $user, Leave $leave): bool
    {
        return $user->isAdmin()
            && $leave->company_id === $user->company_id;
    }

    public function reject(User $user, Leave $leave): bool
    {
        return $user->isAdmin()
            && $leave->company_id === $user->company_id;
    }
}
