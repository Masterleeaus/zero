<?php

declare(strict_types=1);

namespace App\Events\Money;

use App\Models\Money\SupplierPayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierPaymentRecorded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly SupplierPayment $payment) {}
}
