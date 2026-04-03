<?php
declare(strict_types=1);
namespace App\Events\Work;
use App\Models\Money\Quote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class PortalQuoteApproved
{
    use Dispatchable, SerializesModels;
    public function __construct(public readonly Quote $quote) {}
}
