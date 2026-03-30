<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoicePaid;
use App\Notifications\LiveNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class InvoicePaidListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;

        try {
            $owner = User::query()->find($invoice->created_by);

            if ($owner) {
                $owner->notify(new LiveNotification(
                    message: __('Invoice #:number has been paid. Amount: :amount', [
                        'number' => $invoice->invoice_number,
                        'amount' => number_format((float) $invoice->total, 2),
                    ]),
                    link: route('dashboard.money.invoices.show', $invoice),
                    title: __('Invoice Paid'),
                ));
            }
        } catch (\Throwable $th) {
            Log::error('InvoicePaidListener: ' . $th->getMessage(), ['invoice_id' => $invoice->id]);
        }
    }
}
