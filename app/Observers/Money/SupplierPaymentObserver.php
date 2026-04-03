<?php

declare(strict_types=1);

namespace App\Observers\Money;

use App\Models\Money\SupplierPayment;
use App\Services\TitanMoney\AccountingService;
use Illuminate\Support\Facades\Log;

/**
 * SupplierPaymentObserver — auto-posts the AP clearing journal entry.
 *
 * On payment creation:
 *   Dr: Accounts Payable  (liability)
 *   Cr: Bank / Cash       (asset)
 */
class SupplierPaymentObserver
{
    public function __construct(private readonly AccountingService $accounting) {}

    /**
     * Handle the SupplierPayment "created" event.
     */
    public function created(SupplierPayment $payment): void
    {
        try {
            $this->accounting->postSupplierPaymentRecorded($payment);
        } catch (\Throwable $e) {
            Log::error('SupplierPaymentObserver: journal posting failed', [
                'supplier_payment_id' => $payment->id,
                'error'               => $e->getMessage(),
            ]);
        }
    }
}
