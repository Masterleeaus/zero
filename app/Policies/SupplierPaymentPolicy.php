<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierPayment;
use App\Models\User;

class SupplierPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, SupplierPayment $payment): bool
    {
        return $payment->company_id === $user->company_id;
    }

    public function create(User $user, SupplierBill $bill): bool
    {
        return $bill->company_id === $user->company_id
            && ! in_array($bill->status, [SupplierBill::STATUS_PAID, SupplierBill::STATUS_VOID], true);
    }
}
