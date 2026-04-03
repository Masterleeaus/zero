<?php
namespace Modules\BudgetAnalyticsForecasting\Http\Controllers;

use Illuminate\Routing\Controller;

class AdminSettingsController extends Controller
{
    public function show()
    {
        $cfg = config('ai');
        $status = [
            'enabled' => (bool)($cfg['enabled'] ?? false),
            'provider' => $cfg['provider'] ?? 'openai',
            'model' => $cfg['default_model'] ?? 'gpt-4o-mini',
            'has_key' => !empty($cfg['openai']['api_key'] ?? ''),
            'max_months' => $cfg['budget']['max_forecast_months'] ?? 12,
            'safe_mode' => (bool)($cfg['budget']['safe_mode'] ?? true)
        ];
        return view('budgetanalyticsforecasting::admin.ai_settings', compact('cfg','status'));
    }
}
