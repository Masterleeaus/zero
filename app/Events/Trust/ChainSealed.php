<?php

declare(strict_types=1);

namespace App\Events\Trust;

use App\Models\Trust\TrustChainSeal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChainSealed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly TrustChainSeal $seal) {}
}
