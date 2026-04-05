<?php
declare(strict_types=1);
namespace App\Http\Controllers\Core\Money;
use App\Events\Money\BudgetCreated;
use App\Http\Controllers\Controller;
use App\Models\Money\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $budgets = Budget::withoutGlobalScope('company')
            ->where('company_id', $request->user()->company_id)
            ->orderBy('starts_at', 'desc')
            ->get();
        return view('default.panel.user.money.budgets.index', compact('budgets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'starts_at'   => 'required|date',
            'ends_at'     => 'required|date|after:starts_at',
            'notes'       => 'nullable|string',
        ]);
        $budget = Budget::create(array_merge($validated, [
            'company_id' => $request->user()->company_id,
            'status'     => 'draft',
        ]));
        Event::dispatch(new BudgetCreated($budget));
        return redirect()->route('dashboard.money.budgets.index')
            ->with('success', 'Budget created.');
    }
}
