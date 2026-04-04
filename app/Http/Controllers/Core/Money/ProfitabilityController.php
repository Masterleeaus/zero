<?php

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Controller;
use App\Models\Work\ServiceJob;
use App\Services\TitanMoney\ProfitabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfitabilityController extends Controller
{
    public function __construct(protected ProfitabilityService $profitability) {}

    public function index(Request $request)
    {
        return view('money.profitability.index');
    }

    public function job(ServiceJob $job)
    {
        $this->authorize('view', $job);

        $summary = $this->profitability->forJob($job);

        return view('money.profitability.job', compact('job', 'summary'));
    }

    public function byPeriod(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $summary = $this->profitability->forPeriod(
            $request->user()->company_id,
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to'])
        );

        return response()->json($summary);
    }
}
