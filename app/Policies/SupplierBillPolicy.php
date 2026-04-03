<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Money\SupplierBill;
use App\Models\User;

class SupplierBillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, SupplierBill $bill): bool
    {
        return $bill->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, SupplierBill $bill): bool
    {
        return $bill->company_id === $user->company_id
            && $bill->status !== SupplierBill::STATUS_VOID;
    }

    public function delete(User $user, SupplierBill $bill): bool
    {
        return $bill->company_id === $user->company_id
            && $bill->status === SupplierBill::STATUS_DRAFT;
    }

    public function recordPayment(User $user, SupplierBill $bill): bool
    {
        return $bill->company_id === $user->company_id
            && ! in_array($bill->status, [SupplierBill::STATUS_PAID, SupplierBill::STATUS_VOID], true);
    }
}
