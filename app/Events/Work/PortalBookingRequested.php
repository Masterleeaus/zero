<?php
declare(strict_types=1);
namespace App\Events\Work;
use App\Models\Crm\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class PortalBookingRequested
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly Customer $customer, public readonly array $payload = []) {}
}
