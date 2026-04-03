<?php

declare(strict_types=1);

namespace App\Events\Route;

use App\Models\Route\TechnicianAvailability;

class TechnicianAvailabilityUpdated
{
    public function __construct(public readonly TechnicianAvailability $availability) {}
}
