<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Money\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $invoice->company_id === $user->company_id;
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $invoice->company_id === $user->company_id;
    }

    public function markOverdue(User $user, Invoice $invoice): bool
    {
        return $invoice->company_id === $user->company_id;
    }

    public function createFromQuote(User $user, Invoice $invoice = null): bool
    {
        return $user->company_id !== null;
    }
}
