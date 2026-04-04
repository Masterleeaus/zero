<?php

declare(strict_types=1);

namespace App\Listeners\Predict;

use App\Models\Facility\SiteAsset;
use App\Services\Predict\TitanPredictService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateAssetPredictionOnServiceEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(private readonly TitanPredictService $predictService) {}

    /**
     * Handle an asset service event.
     *
     * Designed to receive any event that carries an `assetServiceEvent` property
     * with a `site_asset_id` field (e.g. AgreementServiceConsumed).
     *
     * @param  object  $event
     */
    public function handle(object $event): void
    {
        try {
            $assetId = $event->assetServiceEvent->site_asset_id ?? null;

            if ($assetId === null) {
                return;
            }

            /** @var SiteAsset|null $asset */
            $asset = SiteAsset::find($assetId);

            if ($asset === null) {
                return;
            }

            $this->predictService->generateAssetFailurePrediction($asset);
        } catch (\Throwable $th) {
            Log::error('UpdateAssetPredictionOnServiceEvent: ' . $th->getMessage());
        }
    }
}
