<?php

declare(strict_types=1);

namespace App\Observers\Money;

use App\Models\Money\Payment;

/**
 * PaymentObserver — auto-posting hook preparation (Phase 7).
 *
 * Wired to the `created` lifecycle event on Payment.
 * Full journal auto-posting will be activated in Phase 7 once
 * the default chart of accounts is seeded with standard account codes.
 *
 * Expected journal entry when a Payment is recorded:
 *   Dr: Bank Account (asset)
 *   Cr: Accounts Receivable (asset)
 */
class PaymentObserver
{
    public function __construct(
        private readonly \App\Services\TitanMoney\AccountingService $accounting,
    ) {}

    /**
     * Handle the Payment "created" event — post bank receipt / AR clearance entry.
     */
    public function created(Payment $payment): void
    {
        $this->accounting->postPaymentRecorded($payment);
    }
}
