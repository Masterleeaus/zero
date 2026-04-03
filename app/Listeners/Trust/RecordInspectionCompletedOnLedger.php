<?php

declare(strict_types=1);

namespace App\Listeners\Trust;

use App\Events\Inspection\InspectionCompleted;
use App\Services\Trust\TrustLedgerService;

class RecordInspectionCompletedOnLedger
{
    public function __construct(protected TrustLedgerService $ledger) {}

    public function handle(InspectionCompleted $event): void
    {
        $inspection = $event->inspection;

        $this->ledger->record(
            'inspection_passed',
            $inspection,
            [
                'inspection_id' => $inspection->id,
                'score'         => $inspection->score ?? null,
                'completed_at'  => $inspection->completed_at?->toIso8601String() ?? now()->toIso8601String(),
            ],
        );
    }
}
