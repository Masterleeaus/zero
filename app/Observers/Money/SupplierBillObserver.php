<?php

declare(strict_types=1);

namespace App\Observers\Money;

use App\Models\Money\SupplierBill;
use App\Services\TitanMoney\AccountingService;

/**
 * SupplierBillObserver — auto-posting hook for supplier bill lifecycle.
 *
 * On approval: Dr Operating Expenses / Cr Accounts Payable
 */
class SupplierBillObserver
{
    public function __construct(
        private readonly AccountingService $accounting,
    ) {}

    public function updated(SupplierBill $bill): void
    {
        if ($bill->wasChanged('status') && $bill->status === SupplierBill::STATUS_APPROVED) {
            $this->accounting->postSupplierBillApproved($bill);
        }
    }
}
