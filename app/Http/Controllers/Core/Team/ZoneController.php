<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Support\WorkcoreDemoData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ZoneController extends CoreController
{
    public function index(): View
    {
        return view('default.panel.user.team.zones.index', [
            'zones' => WorkcoreDemoData::zones(),
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.team.zones.form', [
            'zone' => null,
        ]);
    }

    public function show(string $zone): View
    {
        return view('default.panel.user.team.zones.show', [
            'zone' => WorkcoreDemoData::zones()->firstWhere('code', $zone)
                ?? WorkcoreDemoData::zones()->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Zone created.'),
        ]);
    }

    public function edit(string $zone): View
    {
        return view('default.panel.user.team.zones.form', [
            'zone' => WorkcoreDemoData::zones()->firstWhere('code', $zone),
        ]);
    }

    public function update(Request $request, string $zone): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Zone :zone updated.', ['zone' => $zone]),
        ]);
    }

    public function destroy(string $zone): RedirectResponse
    {
        return back()->with([
            'type'    => 'success',
            'message' => __('Zone :zone removed.', ['zone' => $zone]),
        ]);
    }
}
