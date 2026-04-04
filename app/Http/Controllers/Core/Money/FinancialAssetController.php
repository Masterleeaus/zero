<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\FinancialAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialAssetController extends CoreController
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $assets = FinancialAsset::where('company_id', $companyId)
            ->latest('acquisition_date')
            ->paginate(25)
            ->withQueryString();

        return view('default.panel.user.money.financial-assets.index', compact('assets'));
    }

    public function create(Request $request): View
    {
        return view('default.panel.user.money.financial-assets.create', [
            'asset' => new FinancialAsset(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'name'                => 'required|string|max:200',
            'description'         => 'nullable|string',
            'category'            => 'nullable|string|max:100',
            'acquisition_date'    => 'required|date',
            'acquisition_cost'    => 'required|numeric|min:0',
            'depreciation_rate'   => 'required|numeric|min:0|max:1',
            'depreciation_method' => 'nullable|string|in:straight_line',
            'notes'               => 'nullable|string',
        ]);

        $asset = FinancialAsset::create(array_merge($validated, [
            'company_id'    => $companyId,
            'created_by'    => $request->user()->id,
            'current_value' => $validated['acquisition_cost'],
        ]));

        return redirect()
            ->route('dashboard.money.financial-assets.show', $asset)
            ->with('success', __('Financial asset registered.'));
    }

    public function show(Request $request, FinancialAsset $financialAsset): View
    {
        abort_if($financialAsset->company_id !== $request->user()->company_id, 403);

        return view('default.panel.user.money.financial-assets.show', [
            'asset' => $financialAsset,
        ]);
    }

    public function edit(Request $request, FinancialAsset $financialAsset): View
    {
        abort_if($financialAsset->company_id !== $request->user()->company_id, 403);

        return view('default.panel.user.money.financial-assets.edit', [
            'asset' => $financialAsset,
        ]);
    }

    public function update(Request $request, FinancialAsset $financialAsset): RedirectResponse
    {
        abort_if($financialAsset->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'name'                => 'required|string|max:200',
            'description'         => 'nullable|string',
            'category'            => 'nullable|string|max:100',
            'acquisition_date'    => 'required|date',
            'acquisition_cost'    => 'required|numeric|min:0',
            'depreciation_rate'   => 'required|numeric|min:0|max:1',
            'depreciation_method' => 'nullable|string|in:straight_line',
            'notes'               => 'nullable|string',
        ]);

        $financialAsset->update($validated);

        return redirect()
            ->route('dashboard.money.financial-assets.show', $financialAsset)
            ->with('success', __('Financial asset updated.'));
    }

    public function dispose(Request $request, FinancialAsset $financialAsset): RedirectResponse
    {
        abort_if($financialAsset->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'disposal_date'  => 'required|date',
            'disposal_value' => 'required|numeric|min:0',
        ]);

        $financialAsset->update(array_merge($validated, [
            'status' => FinancialAsset::STATUS_DISPOSED,
        ]));

        return back()->with('success', __('Asset disposed.'));
    }
}
