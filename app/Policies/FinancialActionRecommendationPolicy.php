<?php
declare(strict_types=1);
namespace App\Policies;
use App\Models\Money\FinancialActionRecommendation;
use App\Models\User;
class FinancialActionRecommendationPolicy
{
    public function viewAny(User $user): bool { return in_array($user->role, ['admin', 'manager', 'accountant']); }
    public function view(User $user, FinancialActionRecommendation $rec): bool { return $user->company_id === $rec->company_id; }
    public function review(User $user, FinancialActionRecommendation $rec): bool { return $user->company_id === $rec->company_id && in_array($user->role, ['admin', 'manager', 'accountant']); }
}
