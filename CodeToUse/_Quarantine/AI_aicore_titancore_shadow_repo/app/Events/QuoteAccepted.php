<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Money\Quote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuoteAccepted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Quote $quote)
    {
    }
}
