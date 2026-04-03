<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AI\ChatQueryEngine;
use App\Models\AiInsight;
use App\Models\SystemAlert;
use App\Traits\ResolvesStore;

class AIChatController extends Controller
{
    use ResolvesStore;

    /**
     * Show the AI Chat panel.
     */
    public function index(Request $request)
    {
        $store = $this->getActiveStore($request);

        $insights = AiInsight::where('store_id', $store->id)
            ->where('status', 'new')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $alerts = SystemAlert::where('store_id', $store->id)
            ->where('status', 'active')
            ->orderByDesc('severity')
            ->take(5)
            ->get();

        return view('admin.ai.chat', compact('insights', 'alerts', 'store'));
    }

    /**
     * Handle a chat message from the admin.
     */
    public function ask(Request $request, ChatQueryEngine $engine)
    {
        $request->validate(['question' => 'required|string|max:500']);

        $store  = $this->getActiveStore($request);
        $result = $engine->ask($request->input('question'), $store->id);

        return response()->json($result);
    }

    /**
     * Dismiss an AI insight.
     * ✅ Fixed: added store ownership check.
     */
    public function dismissInsight(Request $request, AiInsight $insight)
    {
        $store = $this->getActiveStore($request);
        if ($insight->store_id !== $store->id && !$request->user()->isSuperAdmin()) {
            abort(403, 'You do not have access to this insight.');
        }
        $insight->dismiss();
        return back()->with('success', 'Insight dismissed.');
    }

    /**
     * Resolve a system alert.
     * ✅ Fixed: added store ownership check.
     */
    public function resolveAlert(Request $request, SystemAlert $alert)
    {
        $store = $this->getActiveStore($request);
        if ($alert->store_id !== $store->id && !$request->user()->isSuperAdmin()) {
            abort(403, 'You do not have access to this alert.');
        }
        $alert->resolve();
        return back()->with('success', 'Alert resolved.');
    }
}
