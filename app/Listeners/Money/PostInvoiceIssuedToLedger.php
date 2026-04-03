<?php

declare(strict_types=1);

namespace App\Listeners\Money;

use App\Events\InvoiceIssued;
use App\Services\TitanMoney\AccountingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PostInvoiceIssuedToLedger implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(private readonly AccountingService $accounting)
    {
    }

    public function handle(InvoiceIssued $event): void
    {
        try {
            $this->accounting->postInvoiceIssued($event->invoice);
        } catch (\Throwable $e) {
            Log::error('PostInvoiceIssuedToLedger: ' . $e->getMessage(), [
                'invoice_id' => $event->invoice->id,
            ]);
        }
    }
}
