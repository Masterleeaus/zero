<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Vehicle\Vehicle;
use App\Models\Vehicle\VehicleStock;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleStockReserved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Vehicle      $vehicle,
        public readonly ServiceJob   $job,
        public readonly VehicleStock $stock,
    ) {}
}
