<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Crm;

use App\Http\Controllers\Core\CoreController;
use App\Support\WorkcoreDemoData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DealController extends CoreController
{
    public function index(): View
    {
        return view('default.panel.user.crm.deals.index', [
            'deals' => WorkcoreDemoData::deals(),
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.crm.deals.form', [
            'deal'  => null,
            'stages' => ['prospecting', 'qualification', 'proposal', 'negotiation', 'won', 'lost'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal created.'),
        ]);
    }

    public function show(string $deal): View
    {
        return view('default.panel.user.crm.deals.show', [
            'deal' => WorkcoreDemoData::deals()->firstWhere('id', $deal)
                ?? WorkcoreDemoData::deals()->first(),
        ]);
    }

    public function edit(string $deal): View
    {
        return view('default.panel.user.crm.deals.form', [
            'deal'   => WorkcoreDemoData::deals()->firstWhere('id', $deal),
            'stages' => ['prospecting', 'qualification', 'proposal', 'negotiation', 'won', 'lost'],
        ]);
    }

    public function update(Request $request, string $deal): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal :deal updated.', ['deal' => $deal]),
        ]);
    }

    public function destroy(string $deal): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal :deal archived.', ['deal' => $deal]),
        ]);
    }

    public function kanban(): View
    {
        $deals = WorkcoreDemoData::deals()
            ->groupBy('stage')
            ->sortKeys();

        return view('default.panel.user.crm.deals.kanban', [
            'columns' => $deals,
        ]);
    }

    public function updateStatus(Request $request, string $deal): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Deal :deal status updated.', ['deal' => $deal]),
        ]);
    }
}
