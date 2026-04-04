<?php

declare(strict_types=1);

namespace App\Events\Mesh;

use App\Models\Mesh\MeshDispatchRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeshDispatchCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly MeshDispatchRequest $request,
        public readonly array               $evidenceData,
    ) {}
}
