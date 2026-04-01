<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\ServiceArea;
use App\Models\Work\ServiceAreaBranch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ZoneController extends CoreController
{
    public function index(): View
    {
        $zones = ServiceArea::withCount('sites')
            ->with('branch.district.region')
            ->orderBy('name')
            ->get();

        return view('default.panel.user.team.zones.index', [
            'zones' => $zones,
        ]);
    }

    public function create(): View
    {
        $branches = ServiceAreaBranch::orderBy('name')->get();

        return view('default.panel.user.team.zones.form', [
            'zone'     => null,
            'branches' => $branches,
        ]);
    }

    public function show(ServiceArea $zone): View
    {
        $zone->loadCount('sites')->load('branch.district.region');

        return view('default.panel.user.team.zones.show', [
            'zone' => $zone,
        ]);
    }

    public function show(Territory $zone): View
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'type'        => ['nullable', 'in:zip,suburb,state'],
            'zip_codes'   => ['nullable', 'string'],
            'branch_id'   => ['nullable', 'integer', 'exists:service_area_branches,id'],
        ]);

        ServiceArea::create($validated);

        return redirect()->route('dashboard.team.zones.index')->with([
            'type'    => 'success',
            'message' => __('Zone created.'),
        ]);
    }

    public function edit(ServiceArea $zone): View
    {
        $branches = ServiceAreaBranch::orderBy('name')->get();

        return view('default.panel.user.team.zones.form', [
            'zone'     => $zone,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, ServiceArea $zone): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'type'        => ['nullable', 'in:zip,suburb,state'],
            'zip_codes'   => ['nullable', 'string'],
            'branch_id'   => ['nullable', 'integer', 'exists:service_area_branches,id'],
        ]);

        $zone->update($validated);

        return redirect()->route('dashboard.team.zones.show', $zone)->with([
            'type'    => 'success',
            'message' => __('Zone updated.'),
        ]);
    }

    public function destroy(ServiceArea $zone): RedirectResponse
    {
        $zone->delete();

        return redirect()->route('dashboard.team.zones.index')->with([
            'type'    => 'success',
            'message' => __('Zone removed.'),
        ]);
    }
}
