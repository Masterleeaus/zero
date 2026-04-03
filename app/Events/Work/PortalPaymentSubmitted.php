<?php
declare(strict_types=1);
namespace App\Events\Work;
use App\Models\Money\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class PortalPaymentSubmitted
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly Payment $payment) {}
}
