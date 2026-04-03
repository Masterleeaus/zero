<?php
namespace Modules\BudgetAnalyticsForecasting\Console\Commands;

use Illuminate\Console\Command;
use Modules\BudgetAnalyticsForecasting\AI\ClientInterface;

class AiSmokeCommand extends Command
{
    protected $signature = 'ai:smoke {--months=3}';
    protected $description = 'Run a zero-cost AI capability probe for BudgetAnalyticsForecasting';

    public function handle(ClientInterface $ai)
    {
        $months = (int)$this->option('months');
        $history = [1000, 1150, 1130, 1200];
        $res = $ai->forecast($history, $months, ['probe' => true]);
        $this->info(json_encode($res, JSON_PRETTY_PRINT));
        return self::SUCCESS;
    }
}