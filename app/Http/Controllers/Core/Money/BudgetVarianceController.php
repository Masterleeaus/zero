<?php
declare(strict_types=1);
namespace App\Http\Controllers\Core\Money;
use App\Http\Controllers\Controller;
use App\Services\TitanMoney\BudgetVarianceService;
use Illuminate\Http\Request;

class BudgetVarianceController extends Controller
{
    public function __construct(protected BudgetVarianceService $variance) {}

    public function index(Request $request)
    {
        $report = $this->variance->companySummary($request->user()->company_id);
        return view('default.panel.user.money.budget-variance.index', compact('report'));
    }
}
