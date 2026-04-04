<?php

namespace App\Events\Money;

use App\Models\Money\JobCostAllocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaterialIssuedToJob
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly JobCostAllocation $allocation) {}
}
