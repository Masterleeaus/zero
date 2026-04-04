<?php

namespace App\Policies;

use App\Models\Money\JobCostAllocation;
use App\Models\User;

class JobCostAllocationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JobCostAllocation $allocation): bool
    {
        return $user->company_id === $allocation->company_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin', 'accountant']);
    }

    public function update(User $user, JobCostAllocation $allocation): bool
    {
        return $user->company_id === $allocation->company_id
            && in_array($user->role, ['admin', 'super_admin', 'accountant'])
            && ! $allocation->isPosted();
    }

    public function delete(User $user, JobCostAllocation $allocation): bool
    {
        return $user->company_id === $allocation->company_id
            && in_array($user->role, ['admin', 'super_admin'])
            && ! $allocation->isPosted();
    }
}
