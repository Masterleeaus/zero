<?php

declare(strict_types=1);

namespace App\Events\Mesh;

use App\Models\Mesh\MeshDispatchRequest;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeshDispatchRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly MeshDispatchRequest $request,
        public readonly ServiceJob          $job,
    ) {}
}
