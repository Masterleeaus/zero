<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DispatchStockBlocked
{
    use Dispatchable, SerializesModels;

    /**
     * @param  list<string>  $blockers
     */
    public function __construct(
        public readonly ServiceJob $job,
        public readonly array $blockers,
    ) {}
}
