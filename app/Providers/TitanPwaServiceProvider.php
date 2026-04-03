<?php

namespace App\Providers;

use App\Services\TitanCoreConsensus\EquilibriumResolver;
use App\Services\TitanCoreConsensus\TriCoreConsensus;
use App\Services\TitanZeroPwaSystem\NodeTrustService;
use App\Services\TitanZeroPwaSystem\PwaDeferredReplayService;
use App\Services\TitanZeroPwaSystem\PwaNodeFingerprint;
use App\Services\TitanZeroPwaSystem\PwaQueueHealthService;
use App\Services\TitanZeroPwaSystem\PwaRuntimeContractService;
use App\Services\TitanZeroPwaSystem\PwaStagingService;
use App\Services\TitanZeroPwaSystem\SignalEnvelopeBuilder;
use App\Services\TitanZeroPwaSystem\SignalSignatureValidator;
use App\Services\TitanZeroPwaSystem\TitanPwaManifestService;
use App\Services\TitanZeroPwaSystem\TitanPwaSyncService;
use App\Titan\Signals\EnvelopeBuilder;
use Illuminate\Support\ServiceProvider;

class TitanPwaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Pass 3 services
        $this->app->singleton(PwaRuntimeContractService::class);
        $this->app->singleton(PwaDeferredReplayService::class);
        $this->app->singleton(PwaQueueHealthService::class);
        $this->app->singleton(PwaStagingService::class);

        $this->app->singleton(TitanPwaManifestService::class, function ($app) {
            return new TitanPwaManifestService(
                $app->make(PwaRuntimeContractService::class)
            );
        });

        $this->app->singleton(SignalEnvelopeBuilder::class, function ($app) {
            return new SignalEnvelopeBuilder(
                $app->make(EnvelopeBuilder::class)
            );
        });

        $this->app->singleton(SignalSignatureValidator::class);
        $this->app->singleton(NodeTrustService::class);
        $this->app->singleton(PwaNodeFingerprint::class);

        $this->app->singleton(TriCoreConsensus::class);

        $this->app->singleton(TitanPwaSyncService::class, function ($app) {
            return new TitanPwaSyncService(
                $app->make(SignalEnvelopeBuilder::class),
                $app->make(TriCoreConsensus::class),
                $app->make(SignalSignatureValidator::class),
                $app->make(NodeTrustService::class),
            );
        });

        $this->app->singleton(EquilibriumResolver::class);
    }

    public function boot(): void
    {
    }
}
