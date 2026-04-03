<?php

namespace Modules\Workflow\Providers;

use Modules\Workflow\Conditions\Operators\DateWithin;
use Modules\Workflow\Conditions\Operators\NotEquals;
use Modules\Workflow\Triggers\Eloquent\EloquentDeletedTrigger;
use Modules\Workflow\Triggers\Eloquent\EloquentUpdatedTrigger;
use Modules\Workflow\Triggers\Eloquent\EloquentCreatedTrigger;
use Modules\Workflow\Conditions\Operators\IsEmpty;
use Modules\Workflow\Conditions\Operators\GreaterThan;
use Modules\Workflow\Conditions\Operators\Contains;
use Modules\Workflow\Conditions\Operators\Equals;
use Modules\Workflow\Conditions\ConditionRegistry;
use Modules\Workflow\Console\Commands\WorkflowRunDiagnostics;
use Modules\Workflow\Console\Commands\WorkflowReplayRun;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Workflow\Services\WorkflowEventDispatcher;

class WorkflowServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Workflow';
    protected string $moduleNameLower = 'workflow';

    public function boot(): void
    {
        $this->commands([
            WorkflowReplayRun::class,
            WorkflowRunDiagnostics::class,
        ]);

        $this->registerResources();
        $this->registerRoutes();
        $this->registerEventListeners();
        $this->registerOptionalTitanZeroCapability();
    }

    protected function registerResources(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', $this->moduleNameLower);
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', $this->moduleNameLower);
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', $this->moduleNameLower);
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        if (file_exists(__DIR__ . '/../Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        }
    }

    protected function registerEventListeners(): void
    {
        // System-wide Eloquent lifecycle triggers (wildcard supported by Laravel dispatcher)
        Event::listen('eloquent.created: *', function (string $eventName, array $data) {
            $model = $data[0] ?? null;
            app(WorkflowEventDispatcher::class)->handle($eventName, [
                'model' => is_object($model) ? get_class($model) : null,
                'attributes' => is_object($model) ? $model->getAttributes() : null,
                'company_id' => is_object($model) && isset($model->company_id) ? $model->company_id : null,
                'user_id' => null,
            ]);
        });

        Event::listen('eloquent.updated: *', function (string $eventName, array $data) {
            $model = $data[0] ?? null;
            app(WorkflowEventDispatcher::class)->handle($eventName, [
                'model' => is_object($model) ? get_class($model) : null,
                'attributes' => is_object($model) ? $model->getAttributes() : null,
                'company_id' => is_object($model) && isset($model->company_id) ? $model->company_id : null,
                'user_id' => null,
            ]);
        });

        Event::listen('eloquent.deleted: *', function (string $eventName, array $data) {
            $model = $data[0] ?? null;
            app(WorkflowEventDispatcher::class)->handle($eventName, [
                'model' => is_object($model) ? get_class($model) : null,
                'attributes' => is_object($model) ? $model->getAttributes() : null,
                'company_id' => is_object($model) && isset($model->company_id) ? $model->company_id : null,
                'user_id' => null,
            ]);
        });

        // Allow modules to emit explicit domain events and Workflow will route them.
        Event::listen('*', function (string $eventName, array $data) {
            if (str_starts_with($eventName, 'eloquent.')) return;
            // Keep noise low: only accept events with known prefixes OR explicit workflow.*.
            $allowedPrefixes = ['customerconnect.', 'documents.', 'inspection.', 'assetmanager.', 'titanzero.', 'workflow.'];
            foreach ($allowedPrefixes as $p) {
                if (str_starts_with($eventName, $p)) {
                    app(WorkflowEventDispatcher::class)->handle($eventName, [
                        'data' => $data,
                    ]);
                    break;
                }
            }
        });
    }

    protected function registerOptionalTitanZeroCapability(): void
    {
        // Optional capability registry registration must never break render
        try {
            if (class_exists(\Modules\TitanZero\Services\CapabilityRegistry::class)) {
                \Modules\TitanZero\Services\CapabilityRegistry::registerModuleFromConfig('Workflow');
            }
        } catch (\Throwable $e) {
            // swallow
        }
    }

    public function register(): void
    {
        $this->app->singleton(ConditionRegistry::class, function () {
            $reg = new ConditionRegistry();
            $reg->register(new Equals());
            $reg->register(new Contains());
            $reg->register(new GreaterThan());
            $reg->register(new IsEmpty());
            $reg->register(new NotEquals());
            $reg->register(new DateWithin());
            return $reg;
        });
    }
}
