<?php

declare(strict_types=1);

namespace App\Events\Mesh;

use App\Models\Mesh\MeshNode;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeshTrustChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly MeshNode $node,
        public readonly string   $previousLevel,
        public readonly string   $newLevel,
    ) {}
}
