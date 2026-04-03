<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\FieldServiceSaleApproved;
use App\Services\Work\FieldServiceSaleService;

/**
 * On FieldServiceSaleApproved:
 *   → convert eligible quote lines to ServiceJobs.
 *
 * This listener is intentionally lightweight — it delegates all business
 * logic to FieldServiceSaleService so the pipeline is testable in isolation.
 */
class FieldServiceSaleApprovedListener
{
    public function __construct(protected FieldServiceSaleService $service) {}

    public function handle(FieldServiceSaleApproved $event): void
    {
        $quote = $event->quote;

        if (! $quote->createsFieldWork()) {
            return;
        }

        $this->service->convertQuoteToJobs($quote);
    }
}
