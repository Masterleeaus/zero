<?php
namespace Modules\BudgetAnalyticsForecasting\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\BudgetAnalyticsForecasting\AI\ClientInterface;
use Modules\BudgetAnalyticsForecasting\AI\OpenAIClient;

class AIServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ClientInterface::class, function ($app) {
            // Pulls from config/ai.php (env-backed)
            $cfg = config('ai');
            return new OpenAIClient($cfg);
        });
    }
}