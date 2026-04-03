<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Inventory\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $supplier->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $supplier->company_id === $user->company_id;
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $supplier->company_id === $user->company_id;
    }
}
