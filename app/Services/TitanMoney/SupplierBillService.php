<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Inventory\Supplier;
use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierBillItem;
use Illuminate\Support\Facades\DB;

/**
 * SupplierBillService — Accounts Payable lifecycle management.
 *
 * Responsibilities:
 *   - Create supplier bills (AP documents)
 *   - Approve bills (move to accounts payable liability)
 *   - Record partial / full payments against bills
 *   - Provide aging report data
 */
class SupplierBillService
{
    /**
     * Create a new supplier bill in draft status.
     *
     * @param  array{
     *   company_id: int,
     *   supplier_id: int,
     *   bill_date: string,
     *   due_date: string,
     *   items: array<array{description: string, quantity: float, unit_price: float, account_id?: int}>,
     *   purchase_order_id?: int,
     *   reference?: string,
     *   notes?: string,
     *   created_by?: int,
     * } $payload
     */
    public function create(array $payload): SupplierBill
    {
        return DB::transaction(function () use ($payload): SupplierBill {
            $bill = SupplierBill::create([
                'company_id'        => $payload['company_id'],
                'created_by'        => $payload['created_by'] ?? null,
                'supplier_id'       => $payload['supplier_id'],
                'purchase_order_id' => $payload['purchase_order_id'] ?? null,
                'bill_number'       => $this->nextBillNumber($payload['company_id']),
                'reference'         => $payload['reference'] ?? null,
                'bill_date'         => $payload['bill_date'],
                'due_date'          => $payload['due_date'],
                'notes'             => $payload['notes'] ?? null,
                'status'            => SupplierBill::STATUS_DRAFT,
            ]);

            foreach (($payload['items'] ?? []) as $item) {
                $qty   = (float) ($item['quantity'] ?? 1);
                $price = (float) ($item['unit_price'] ?? 0);
                $tax   = (float) ($item['tax_amount'] ?? 0);

                SupplierBillItem::create([
                    'company_id'       => $payload['company_id'],
                    'supplier_bill_id' => $bill->id,
                    'description'      => $item['description'] ?? '',
                    'quantity'         => $qty,
                    'unit_price'       => $price,
                    'amount'           => round($qty * $price, 2),
                    'tax_amount'       => $tax,
                    'account_id'       => $item['account_id'] ?? null,
                ]);
            }

            $bill->recalculate();

            return $bill->fresh();
        });
    }

    /**
     * Approve a draft supplier bill (moves liability to AP).
     */
    public function approve(SupplierBill $bill, int $approvedBy): SupplierBill
    {
        if (! $bill->isDraft()) {
            throw new \RuntimeException("Only draft bills can be approved. Current status: {$bill->status}");
        }

        $bill->update([
            'status'      => SupplierBill::STATUS_APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $bill->fresh();
    }

    /**
     * Record a payment against a supplier bill.
     *
     * @param  float $amount Amount being paid now.
     */
    public function recordPayment(SupplierBill $bill, float $amount): SupplierBill
    {
        if ($bill->isPaid()) {
            throw new \RuntimeException('Bill is already fully paid.');
        }

        $newPaid = (float) $bill->amount_paid + $amount;

        $status = $newPaid >= (float) $bill->total_amount
            ? SupplierBill::STATUS_PAID
            : SupplierBill::STATUS_APPROVED;

        $bill->update([
            'amount_paid' => $newPaid,
            'status'      => $status,
            'paid_at'     => $status === SupplierBill::STATUS_PAID ? now() : $bill->paid_at,
        ]);

        return $bill->fresh();
    }

    /**
     * Vendor aging summary: buckets overdue bills by age bands.
     *
     * @return array<string, float> Keys: current, 1_30, 31_60, 61_90, over_90
     */
    public function agingSummary(int $companyId): array
    {
        $today = now()->startOfDay();

        $buckets = [
            'current' => 0.0,
            '1_30'    => 0.0,
            '31_60'   => 0.0,
            '61_90'   => 0.0,
            'over_90' => 0.0,
        ];

        SupplierBill::where('company_id', $companyId)
            ->unpaid()
            ->get()
            ->each(function (SupplierBill $bill) use ($today, &$buckets): void {
                $balance = $bill->balanceDue();
                if ($balance <= 0) {
                    return;
                }

                if ($bill->due_date === null || $bill->due_date->isFuture()) {
                    $buckets['current'] += $balance;
                    return;
                }

                // diffInDays(date, false) returns negative when $bill->due_date is in the past.
                // Multiply by -1 to get a positive "days overdue" value for bucket comparison.
                $days = (int) $today->diffInDays($bill->due_date, false) * -1;

                if ($days <= 30) {
                    $buckets['1_30'] += $balance;
                } elseif ($days <= 60) {
                    $buckets['31_60'] += $balance;
                } elseif ($days <= 90) {
                    $buckets['61_90'] += $balance;
                } else {
                    $buckets['over_90'] += $balance;
                }
            });

        return $buckets;
    }

    // -----------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------

    private function nextBillNumber(int $companyId): string
    {
        $count = SupplierBill::where('company_id', $companyId)->withTrashed()->count() + 1;
        return 'BILL-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a supplier bill draft from a completed/partial purchase order.
     * Idempotent — returns existing non-cancelled bill if one already exists.
     */
    public function createFromPurchaseOrder(\App\Models\Inventory\PurchaseOrder $po, array $overrides = []): SupplierBill
    {
        return DB::transaction(function () use ($po, $overrides): SupplierBill {
            $existing = SupplierBill::withoutGlobalScopes()
                ->where('purchase_order_id', $po->id)
                ->where('company_id', $po->company_id)
                ->whereNotIn('status', ['cancelled'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $bill = SupplierBill::create(array_merge([
                'company_id'        => $po->company_id,
                'created_by'        => $po->created_by,
                'supplier_id'       => $po->supplier_id,
                'purchase_order_id' => $po->id,
                'bill_number'       => $this->nextBillNumber($po->company_id),
                'reference'         => $po->po_number,
                'bill_date'         => now()->toDateString(),
                'due_date'          => now()->addDays(30)->toDateString(),
                'status'            => SupplierBill::STATUS_DRAFT,
                'subtotal'          => $po->subtotal,
                'tax_amount'        => $po->tax_amount,
                'total_amount'      => $po->total_amount,
                'amount_paid'       => 0,
                'currency'          => $po->currency_code ?? 'AUD',
                'notes'             => "Generated from PO {$po->po_number}",
            ], $overrides));

            foreach ($po->items as $item) {
                $qty = $item->qty_received > 0 ? $item->qty_received : $item->qty_ordered;

                SupplierBillItem::create([
                    'company_id'       => $po->company_id,
                    'supplier_bill_id' => $bill->id,
                    'description'      => $item->description ?? ($item->inventoryItem?->name ?? 'Item'),
                    'quantity'         => $qty,
                    'unit_price'       => $item->unit_price,
                    'amount'           => round($qty * $item->unit_price, 2),
                ]);
            }

            return $bill->fresh(['items']);
        });
    }
}
