<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\ServiceAreaRegion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceAreaRegionController extends CoreController
{
    public function index(): View
    {
        $regions = ServiceAreaRegion::withCount('districts')
            ->orderBy('name')
            ->get();

        return view('default.panel.user.team.service-areas.regions.index', [
            'regions' => $regions,
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.team.service-areas.regions.form', [
            'region' => null,
        ]);
    }

    public function show(ServiceAreaRegion $region): View
    {
        $region->loadCount('districts')->load('districts');

        return view('default.panel.user.team.service-areas.regions.show', [
            'region' => $region,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        ServiceAreaRegion::create($validated);

        return redirect()->route('dashboard.team.service-area-regions.index')->with([
            'type'    => 'success',
            'message' => __('Region created.'),
        ]);
    }

    public function edit(ServiceAreaRegion $region): View
    {
        return view('default.panel.user.team.service-areas.regions.form', [
            'region' => $region,
        ]);
    }

    public function update(Request $request, ServiceAreaRegion $region): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $region->update($validated);

        return redirect()->route('dashboard.team.service-area-regions.show', $region)->with([
            'type'    => 'success',
            'message' => __('Region updated.'),
        ]);
    }

    public function destroy(ServiceAreaRegion $region): RedirectResponse
    {
        $region->delete();

        return redirect()->route('dashboard.team.service-area-regions.index')->with([
            'type'    => 'success',
            'message' => __('Region removed.'),
        ]);
    }
}
