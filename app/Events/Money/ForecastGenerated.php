<?php

declare(strict_types=1);

namespace App\Events\Money;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ForecastGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $companyId,
        public readonly array $forecast,
    ) {}
}
