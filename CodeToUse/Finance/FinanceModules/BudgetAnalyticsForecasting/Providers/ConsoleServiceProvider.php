<?php
namespace Modules\BudgetAnalyticsForecasting\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\BudgetAnalyticsForecasting\Console\Commands\AiSmokeCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register(){}
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([ AiSmokeCommand::class ]);
        }
    }
}