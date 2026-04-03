<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Money\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Account $account): bool
    {
        return $account->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Account $account): bool
    {
        return $account->company_id === $user->company_id;
    }

    public function delete(User $user, Account $account): bool
    {
        return $account->company_id === $user->company_id;
    }
}
