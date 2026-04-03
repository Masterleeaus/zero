<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractExpired
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceAgreement $agreement,
    ) {}
}
