<?php

declare(strict_types=1);

namespace App\Events\Docs;

use App\Models\Premises\FacilityDocument;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentReviewDue
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly FacilityDocument $document,
    ) {}
}
