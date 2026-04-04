<?php

namespace App\Providers;

use App\Contracts\TitanIntegration\ZeroSignalBridgeContract;
use App\Titan\Signals\ApprovalChain;
use App\Titan\Signals\AuditTrail;
use App\Titan\Signals\EnvelopeBuilder;
use App\Titan\Signals\ProcessRecorder;
use App\Titan\Signals\SignalPriorityEngine;
use App\Titan\Signals\SignalRegistry;
use App\Titan\Signals\ProcessStateMachine;
use App\Titan\Signals\SignalDispatcher;
use App\Titan\Signals\SignalNormalizer;
use App\Titan\Signals\SignalsService;
use App\Titan\Signals\SignalValidator;
use App\Titan\Signals\Subscribers\PulseSubscriber;
use App\Titan\Signals\Subscribers\RewindSubscriber;
use App\Titan\Signals\Subscribers\ZeroSubscriber;
use Illuminate\Support\ServiceProvider;

class TitanSignalsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(base_path('config/titan_signal.php'), 'titan_signal');

        $this->app->singleton(SignalNormalizer::class);
        $this->app->singleton(SignalRegistry::class);
        $this->app->singleton(SignalPriorityEngine::class);
        $this->app->singleton(SignalValidator::class);
        $this->app->singleton(ApprovalChain::class);
        $this->app->singleton(AuditTrail::class);
        $this->app->singleton(ProcessStateMachine::class);
        $this->app->singleton(ProcessRecorder::class);
        $this->app->singleton(EnvelopeBuilder::class);
        $this->app->singleton(ZeroSubscriber::class);
        $this->app->singleton(PulseSubscriber::class);
        $this->app->singleton(RewindSubscriber::class);
        $this->app->singleton(SignalDispatcher::class);
        $this->app->singleton(SignalsService::class);
        $this->app->bind(ZeroSignalBridgeContract::class, SignalsService::class);
    }

    public function boot(): void
    {
    }
}
