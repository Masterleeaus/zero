<?php

declare(strict_types=1);

namespace App\Events\Money;

use App\Models\Inventory\PurchaseOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderIssued
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly PurchaseOrder $purchaseOrder) {}
}
