<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderService
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function generatePoNumber(int $companyId): string
    {
        $year = now()->year;

        $maxId = (int) PurchaseOrder::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->max('id');

        $seq = str_pad((string) ($maxId + 1), 4, '0', STR_PAD_LEFT);

        return "PO-{$year}-{$seq}";
    }

    public function createPurchaseOrder(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $data['po_number'] = $this->generatePoNumber($data['company_id']);
            $po = PurchaseOrder::create($data);

            foreach ($items as $item) {
                $item['purchase_order_id'] = $po->id;
                $item['line_total'] = ($item['qty_ordered'] ?? 0) * ($item['unit_price'] ?? 0);
                PurchaseOrderItem::create($item);
            }

            $this->calculateTotals($po);

            return $po->fresh(['items', 'supplier']);
        });
    }

    public function receivePurchaseOrder(PurchaseOrder $po, array $receivedItems): void
    {
        DB::transaction(function () use ($po, $receivedItems) {
            $allReceived = true;
            $anyReceived = false;

            foreach ($receivedItems as $lineData) {
                $line = PurchaseOrderItem::withoutGlobalScopes()
                    ->where('purchase_order_id', $po->id)
                    ->where('id', $lineData['id'])
                    ->first();

                if (! $line) {
                    Log::warning('PurchaseOrderService::receivePurchaseOrder — line not found', [
                        'purchase_order_id' => $po->id,
                        'line_id'           => $lineData['id'] ?? null,
                    ]);
                    continue;
                }

                $qtyReceiving = (int) ($lineData['qty_receiving'] ?? 0);
                if ($qtyReceiving <= 0) {
                    continue;
                }

                $anyReceived = true;
                $newReceived = $line->qty_received + $qtyReceiving;
                $line->update(['qty_received' => $newReceived]);

                if ($newReceived < $line->qty_ordered) {
                    $allReceived = false;
                }

                if ($line->item_id && ! empty($lineData['warehouse_id'])) {
                    $this->stockService->recordMovement([
                        'company_id'        => $po->company_id,
                        'created_by'        => $po->created_by,
                        'item_id'           => $line->item_id,
                        'warehouse_id'      => $lineData['warehouse_id'],
                        'purchase_order_id' => $po->id,
                        'type'              => 'in',
                        'qty_change'        => $qtyReceiving,
                        'reference'         => $po->po_number,
                        'note'              => "Received via PO {$po->po_number}",
                        'cost_per_unit'     => $line->unit_price,
                    ]);
                }
            }

            if ($anyReceived) {
                $newStatus = $allReceived ? 'received' : 'partial';
                $po->update(['status' => $newStatus]);
            }
        });
    }

    public function calculateTotals(PurchaseOrder $po): void
    {
        $items = PurchaseOrderItem::withoutGlobalScopes()
            ->where('purchase_order_id', $po->id)
            ->get();

        $subtotal = $items->sum('line_total');
        $taxAmount = $items->sum(fn ($item) => $item->line_total * ($item->tax_rate / 100));

        $po->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
        ]);
    }
}
