<?php

declare(strict_types=1);

namespace App\Events\Trust;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChainTamperingDetected
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $tampered  Summary of tampered entries.
     */
    public function __construct(
        public readonly int $companyId,
        public readonly array $tampered,
    ) {}
}
