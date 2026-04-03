<?php

declare(strict_types=1);

namespace App\Events\Money;

use App\Models\Money\SupplierBill;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierBillRecorded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly SupplierBill $bill) {}
}
