<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Inventory\Supplier;

class SupplierService
{
    public function createSupplier(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier->fresh();
    }

    public function getSupplierBalance(Supplier $supplier): array
    {
        $orders = $supplier->purchaseOrders()->withoutGlobalScopes()->get();

        $totalOrdered = $orders->sum('total_amount');
        $receivedOrders = $orders->whereIn('status', ['received', 'partial']);
        $pendingOrders  = $orders->whereIn('status', ['draft', 'sent']);

        return [
            'total_orders'   => $orders->count(),
            'total_amount'   => $totalOrdered,
            'received_count' => $receivedOrders->count(),
            'pending_count'  => $pendingOrders->count(),
            'pending_amount' => $pendingOrders->sum('total_amount'),
        ];
    }
}
