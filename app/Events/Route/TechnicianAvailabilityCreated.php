<?php

declare(strict_types=1);

namespace App\Events\Route;

use App\Models\Route\TechnicianAvailability;

class TechnicianAvailabilityCreated
{
    public function __construct(public readonly TechnicianAvailability $availability) {}
}
