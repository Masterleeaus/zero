<?php

declare(strict_types=1);

namespace App\Events\Docs;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MandatoryDocumentAcknowledged
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  Model  $pivot  JobInjectedDocument or InspectionInjectedDocument
     */
    public function __construct(
        public readonly Model $pivot,
        public readonly User  $acknowledgedBy,
    ) {}
}
