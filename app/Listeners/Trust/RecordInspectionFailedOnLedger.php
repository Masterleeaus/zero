<?php

declare(strict_types=1);

namespace App\Listeners\Trust;

use App\Events\Inspection\InspectionFailed;
use App\Services\Trust\TrustLedgerService;

class RecordInspectionFailedOnLedger
{
    public function __construct(protected TrustLedgerService $ledger) {}

    public function handle(InspectionFailed $event): void
    {
        $inspection = $event->inspection;

        $this->ledger->record(
            'inspection_failed',
            $inspection,
            [
                'inspection_id' => $inspection->id,
                'findings'      => $inspection->findings ?? null,
                'failed_at'     => now()->toIso8601String(),
            ],
        );
    }
}
