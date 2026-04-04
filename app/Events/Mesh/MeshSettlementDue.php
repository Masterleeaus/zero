<?php

declare(strict_types=1);

namespace App\Events\Mesh;

use App\Models\Mesh\MeshSettlement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeshSettlementDue
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly MeshSettlement $settlement,
    ) {}
}
