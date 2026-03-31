<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Support\WorkcoreDemoData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaxController extends CoreController
{
    public function index(): View
    {
        return view('default.panel.user.money.taxes.index', [
            'taxes' => WorkcoreDemoData::taxes(),
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.money.taxes.form', [
            'tax' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Tax rate created.'),
        ]);
    }

    public function show(string $tax): View
    {
        return view('default.panel.user.money.taxes.show', [
            'tax' => WorkcoreDemoData::taxes()->firstWhere('name', $tax)
                ?? WorkcoreDemoData::taxes()->first(),
        ]);
    }

    public function edit(string $tax): View
    {
        return view('default.panel.user.money.taxes.form', [
            'tax' => WorkcoreDemoData::taxes()->firstWhere('name', $tax),
        ]);
    }

    public function update(Request $request, string $tax): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Tax rate :tax updated.', ['tax' => $tax]),
        ]);
    }

    public function destroy(string $tax): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Tax rate :tax removed.', ['tax' => $tax]),
        ]);
    }

    public function setDefault(string $tax): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Tax rate :tax set as default.', ['tax' => $tax]),
        ]);
    }
}
