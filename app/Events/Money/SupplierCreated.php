<?php

declare(strict_types=1);

namespace App\Events\Money;

use App\Models\Inventory\Supplier;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Supplier $supplier) {}
}
