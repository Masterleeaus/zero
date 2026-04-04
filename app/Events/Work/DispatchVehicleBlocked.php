<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Vehicle\Vehicle;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DispatchVehicleBlocked
{
    use Dispatchable, SerializesModels;

    /**
     * @param  list<string>  $reasons
     */
    public function __construct(
        public readonly ServiceJob $job,
        public readonly Vehicle $vehicle,
        public readonly array $reasons,
    ) {}
}
