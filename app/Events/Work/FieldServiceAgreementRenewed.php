<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\FieldServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FieldServiceAgreementRenewed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly FieldServiceAgreement $agreement,
        public readonly FieldServiceAgreement $renewal,
    ) {}
}
