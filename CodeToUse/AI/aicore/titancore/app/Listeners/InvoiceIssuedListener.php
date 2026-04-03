<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceIssued;
use App\Notifications\LiveNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class InvoiceIssuedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(InvoiceIssued $event): void
    {
        $invoice = $event->invoice;

        try {
            $owner = User::query()->find($invoice->created_by);

            if ($owner) {
                $owner->notify(new LiveNotification(
                    message: __('Invoice #:number has been issued.', ['number' => $invoice->invoice_number]),
                    link: route('dashboard.money.invoices.show', $invoice),
                    title: __('Invoice Issued'),
                ));
            }
        } catch (\Throwable $th) {
            Log::error('InvoiceIssuedListener: ' . $th->getMessage(), ['invoice_id' => $invoice->id]);
        }
    }
}
