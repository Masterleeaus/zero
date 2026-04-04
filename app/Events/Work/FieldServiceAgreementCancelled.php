<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Work\FieldServiceAgreement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FieldServiceAgreementCancelled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly FieldServiceAgreement $agreement,
        public readonly string $reason = '',
    ) {}
}
