<?php

declare(strict_types=1);

namespace App\Listeners\Trust;

use App\Models\Facility\AssetServiceEvent;
use App\Services\Trust\TrustLedgerService;

class RecordAssetServiceOnLedger
{
    public function __construct(protected TrustLedgerService $ledger) {}

    /**
     * Called directly (not via event bus) or wired to a future AssetServiced event.
     */
    public function record(AssetServiceEvent $event): void
    {
        $this->ledger->record(
            'asset_serviced',
            $event,
            [
                'event_id'     => $event->id,
                'event_type'   => $event->event_type,
                'site_asset_id' => $event->site_asset_id,
                'serviced_at'  => $event->event_date?->toIso8601String() ?? now()->toIso8601String(),
            ],
        );
    }
}
