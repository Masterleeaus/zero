<?php

namespace App\Providers;

use App\Services\TitanCoreConsensus\EquilibriumResolver;
use App\Services\TitanCoreConsensus\TriCoreConsensus;
use App\Services\TitanZeroPwaSystem\NodeTrustService;
use App\Services\TitanZeroPwaSystem\PwaNodeFingerprint;
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
        $this->app->singleton(TitanPwaManifestService::class);

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
