<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\ContractSLABreach;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractSLABreached
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceAgreement $agreement,
        public readonly ServiceJob $job,
        public readonly ContractSLABreach $breach,
    ) {}
}
