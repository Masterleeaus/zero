<?php

declare(strict_types=1);

namespace App\Events\Inventory;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaterialIssuedToJob
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $companyId,
        public readonly int $serviceJobId,
        public readonly int $itemId,
        public readonly int $qty,
        public readonly float $costPerUnit,
    ) {}
}
