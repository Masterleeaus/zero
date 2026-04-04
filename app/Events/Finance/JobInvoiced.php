<?php

declare(strict_types=1);

namespace App\Events\Finance;

use App\Models\Money\Invoice;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobInvoiced
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly Invoice $invoice,
    ) {}
}
