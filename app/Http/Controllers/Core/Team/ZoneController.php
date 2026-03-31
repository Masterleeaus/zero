<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\Branch;
use App\Models\Work\Territory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ZoneController extends CoreController
{
    public function index(): View
    {
        $territories = Territory::with('branch.district.region')
            ->forSelect()
            ->paginate(30);

        return view('default.panel.user.team.zones.index', [
            'territories' => $territories,
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.team.zones.form', [
            'territory' => new Territory(),
            'branches'  => Branch::forSelect()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'branch_id'   => ['nullable', 'integer', 'exists:branches,id'],
            'type'        => ['nullable', 'string', 'in:zip,state,country'],
            'zip_codes'   => ['nullable', 'string'],
        ]);

        Territory::create($validated);

        return redirect()->route('dashboard.team.zones.index')->with([
            'type'    => 'success',
            'message' => __('Territory created.'),
        ]);
    }

    public function show(Territory $zone): View
    {
        $zone->load('branch.district.region', 'sites');

        return view('default.panel.user.team.zones.show', [
            'territory' => $zone,
        ]);
    }

    public function edit(Territory $zone): View
    {
        return view('default.panel.user.team.zones.form', [
            'territory' => $zone,
            'branches'  => Branch::forSelect()->get(),
        ]);
    }

    public function update(Request $request, Territory $zone): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'branch_id'   => ['nullable', 'integer', 'exists:branches,id'],
            'type'        => ['nullable', 'string', 'in:zip,state,country'],
            'zip_codes'   => ['nullable', 'string'],
        ]);

        $zone->update($validated);

        return redirect()->route('dashboard.team.zones.show', $zone)->with([
            'type'    => 'success',
            'message' => __('Territory updated.'),
        ]);
    }

    public function destroy(Territory $zone): RedirectResponse
    {
        $zone->delete();

        return redirect()->route('dashboard.team.zones.index')->with([
            'type'    => 'success',
            'message' => __('Territory removed.'),
        ]);
    }
}
