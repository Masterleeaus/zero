<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Money\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function update(User $user, Expense $expense): bool
    {
        return $expense->company_id === $user->company_id
            && $user->hasAnyRole(['admin', 'super_admin']);
    }
}
