<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Events\Money\SupplierBillRecorded;
use App\Events\Money\SupplierPaymentRecorded;
use App\Models\Inventory\Supplier;
use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierBillLine;
use App\Models\Money\SupplierPayment;
use Illuminate\Support\Facades\DB;

/**
 * SupplierBillService — orchestrates the AP lifecycle.
 *
 * Responsibilities:
 *   - Create / update supplier bills and their lines
 *   - Record payments against bills
 *   - Trigger journal posting via AccountingService
 *   - Dispatch domain events
 */
class SupplierBillService
{
    public function __construct(private readonly AccountingService $accounting) {}

    // -----------------------------------------------------------------------
    // Supplier Bills
    // -----------------------------------------------------------------------

    /**
     * Create a supplier bill with optional line items.
     *
     * $data keys: supplier_id, purchase_order_id, reference, bill_date, due_date,
     *             currency, subtotal, tax_total, total, status, notes
     * $lines[]: account_id, service_job_id, description, amount, tax_rate, tax_amount
     */
    public function createBill(array $data, array $lines = []): SupplierBill
    {
        return DB::transaction(function () use ($data, $lines): SupplierBill {
            $bill = SupplierBill::create($data);

            foreach ($lines as $line) {
                $bill->lines()->create($line);
            }

            $bill->refresh();

            // Auto-post journal entry when bill is not a draft
            if ($bill->status !== SupplierBill::STATUS_DRAFT) {
                $this->accounting->postSupplierBillRecorded($bill);
            }

            event(new SupplierBillRecorded($bill));

            return $bill;
        });
    }

    /**
     * Update an existing supplier bill.
     */
    public function updateBill(SupplierBill $bill, array $data, array $lines = []): SupplierBill
    {
        return DB::transaction(function () use ($bill, $data, $lines): SupplierBill {
            $bill->update($data);

            if (! empty($lines)) {
                $bill->lines()->delete();
                foreach ($lines as $line) {
                    $bill->lines()->create($line);
                }
            }

            $bill->refresh();

            // Post journal if transitioning to awaiting_payment
            if ($bill->wasChanged('status') && $bill->status === SupplierBill::STATUS_AWAITING_PAYMENT) {
                $this->accounting->postSupplierBillRecorded($bill);
            }

            return $bill;
        });
    }

    /**
     * Recalculate and persist subtotal/tax_total/total from lines.
     */
    public function recalculateTotals(SupplierBill $bill): SupplierBill
    {
        $lines    = $bill->lines()->get();
        $subtotal = $lines->sum('amount');
        $taxTotal = $lines->sum('tax_amount');

        $bill->update([
            'subtotal'  => $subtotal,
            'tax_total' => $taxTotal,
            'total'     => $subtotal + $taxTotal,
        ]);

        return $bill->fresh();
    }

    // -----------------------------------------------------------------------
    // Supplier Payments
    // -----------------------------------------------------------------------

    /**
     * Record a payment against a supplier bill.
     *
     * $data keys: company_id, created_by, payment_account_id, amount, payment_date, reference, notes
     */
    public function recordPayment(SupplierBill $bill, array $data): SupplierPayment
    {
        return DB::transaction(function () use ($bill, $data): SupplierPayment {
            $payment = $bill->payments()->create(array_merge($data, [
                'supplier_bill_id' => $bill->id,
            ]));

            // Update bill paid amount and status
            $newPaid = (float) $bill->amount_paid + (float) $payment->amount;
            $status  = $newPaid >= (float) $bill->total
                ? SupplierBill::STATUS_PAID
                : SupplierBill::STATUS_PARTIAL;

            $bill->update([
                'amount_paid' => $newPaid,
                'status'      => $status,
            ]);

            // Post journal: Dr AP / Cr Bank
            $this->accounting->postSupplierPaymentRecorded($payment);

            event(new SupplierPaymentRecorded($payment));

            return $payment;
        });
    }

    // -----------------------------------------------------------------------
    // Supplier helpers
    // -----------------------------------------------------------------------

    /**
     * Return outstanding bills for a given supplier.
     */
    public function outstandingBills(Supplier $supplier): \Illuminate\Database\Eloquent\Collection
    {
        return SupplierBill::query()
            ->where('supplier_id', $supplier->id)
            ->whereNotIn('status', [SupplierBill::STATUS_PAID, SupplierBill::STATUS_VOID])
            ->orderBy('due_date')
            ->get();
    }
}
