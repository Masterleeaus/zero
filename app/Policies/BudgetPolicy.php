<?php
declare(strict_types=1);
namespace App\Policies;
use App\Models\Money\Budget;
use App\Models\User;
class BudgetPolicy
{
    public function viewAny(User $user): bool { return in_array($user->role, ['admin', 'manager', 'accountant']); }
    public function view(User $user, Budget $budget): bool { return $user->company_id === $budget->company_id; }
    public function create(User $user): bool { return in_array($user->role, ['admin', 'manager', 'accountant']); }
    public function update(User $user, Budget $budget): bool { return $user->company_id === $budget->company_id && in_array($user->role, ['admin', 'manager', 'accountant']); }
    public function delete(User $user, Budget $budget): bool { return $user->company_id === $budget->company_id && $user->role === 'admin'; }
}
