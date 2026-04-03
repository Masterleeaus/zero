<?php

declare(strict_types=1);

namespace App\Listeners\Money;

use App\Events\Money\PaymentRecorded;
use App\Services\TitanMoney\AccountingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PostPaymentRecordedToLedger implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(private readonly AccountingService $accounting)
    {
    }

    public function handle(PaymentRecorded $event): void
    {
        try {
            $this->accounting->postPaymentRecorded($event->payment);
        } catch (\Throwable $e) {
            Log::error('PostPaymentRecordedToLedger: ' . $e->getMessage(), [
                'payment_id' => $event->payment->id,
            ]);
        }
    }
}
