<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Money\Invoice;
use App\Models\Money\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $payment->company_id === $user->company_id;
    }

    public function record(User $user, Invoice $invoice): bool
    {
        return $invoice->company_id === $user->company_id;
    }
}
