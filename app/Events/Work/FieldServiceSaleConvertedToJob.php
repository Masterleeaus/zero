<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\Money\Quote;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a Quote acceptance results in one or more ServiceJobs being created.
 *
 * Mirrors Odoo fieldservice_sale: _field_service_generation creates fsm.order records.
 */
class FieldServiceSaleConvertedToJob
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, ServiceJob>  $jobs
     */
    public function __construct(
        public readonly Quote $quote,
        public readonly \Illuminate\Database\Eloquent\Collection $jobs,
    ) {}
}
