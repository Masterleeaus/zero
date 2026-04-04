<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Money\Quote;
use App\Models\User;

class QuotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Quote $quote): bool
    {
        return $quote->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Quote $quote): bool
    {
        return $quote->company_id === $user->company_id;
    }

    public function convert(User $user, Quote $quote): bool
    {
        return $quote->company_id === $user->company_id;
    }

    public function changeStatus(User $user, Quote $quote): bool
    {
        return $quote->company_id === $user->company_id;
    }
}
