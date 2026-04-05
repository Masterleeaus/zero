<?php
declare(strict_types=1);
namespace App\Http\Controllers\Core\Money;
use App\Http\Controllers\Controller;
use App\Models\Money\FinancialActionRecommendation;
use App\Services\TitanMoney\FinancialApprovalBridgeService;
use Illuminate\Http\Request;

class FinancialRecommendationController extends Controller
{
    public function __construct(protected FinancialApprovalBridgeService $bridge) {}

    public function index(Request $request)
    {
        $queue = $this->bridge->queue($request->user()->company_id);
        return view('default.panel.user.money.recommendations.index', compact('queue'));
    }

    public function review(Request $request, FinancialActionRecommendation $recommendation)
    {
        $this->authorize('review', $recommendation);
        return view('default.panel.user.money.recommendations.review', compact('recommendation'));
    }

    public function approve(Request $request, FinancialActionRecommendation $recommendation)
    {
        $this->authorize('review', $recommendation);
        $validated = $request->validate(['review_notes' => 'nullable|string|max:2000']);
        $this->bridge->approve($recommendation->id, $request->user(), $validated['review_notes'] ?? null);
        return redirect()->route('dashboard.money.recommendations.index')
            ->with('success', 'Recommendation approved.');
    }

    public function reject(Request $request, FinancialActionRecommendation $recommendation)
    {
        $this->authorize('review', $recommendation);
        $validated = $request->validate(['review_notes' => 'nullable|string|max:2000']);
        $this->bridge->reject($recommendation->id, $request->user(), $validated['review_notes'] ?? null);
        return redirect()->route('dashboard.money.recommendations.index')
            ->with('success', 'Recommendation rejected.');
    }

    public function dismiss(Request $request, FinancialActionRecommendation $recommendation)
    {
        $this->authorize('review', $recommendation);
        $validated = $request->validate(['review_notes' => 'nullable|string|max:2000']);
        $this->bridge->dismiss($recommendation->id, $request->user(), $validated['review_notes'] ?? null);
        return redirect()->route('dashboard.money.recommendations.index')
            ->with('success', 'Recommendation dismissed.');
    }
}
