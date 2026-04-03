<?php
declare(strict_types=1);
namespace App\Events\Work;
use App\Models\Crm\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class PortalMessageCreated
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly Customer $customer, public readonly array $message = []) {}
}
