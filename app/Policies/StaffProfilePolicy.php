<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Work\StaffProfile;

class StaffProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, StaffProfile $staffProfile): bool
    {
        return $staffProfile->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, StaffProfile $staffProfile): bool
    {
        return $staffProfile->company_id === $user->company_id
            && ($user->isAdmin() || $staffProfile->user_id === $user->id);
    }

    public function delete(User $user, StaffProfile $staffProfile): bool
    {
        return $user->isAdmin()
            && $staffProfile->company_id === $user->company_id;
    }
}
