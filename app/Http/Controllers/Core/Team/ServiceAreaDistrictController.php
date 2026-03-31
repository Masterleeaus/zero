<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\ServiceAreaDistrict;
use App\Models\Work\ServiceAreaRegion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceAreaDistrictController extends CoreController
{
    public function index(): View
    {
        $districts = ServiceAreaDistrict::with('region')
            ->withCount('branches')
            ->orderBy('name')
            ->get();

        return view('default.panel.user.team.service-areas.districts.index', [
            'districts' => $districts,
        ]);
    }

    public function create(): View
    {
        $regions = ServiceAreaRegion::orderBy('name')->get();

        return view('default.panel.user.team.service-areas.districts.form', [
            'district' => null,
            'regions'  => $regions,
        ]);
    }

    public function show(ServiceAreaDistrict $district): View
    {
        $district->loadCount('branches')->load('region', 'branches');

        return view('default.panel.user.team.service-areas.districts.show', [
            'district' => $district,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'region_id'       => ['nullable', 'integer', 'exists:service_area_regions,id'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        ServiceAreaDistrict::create($validated);

        return redirect()->route('dashboard.team.service-area-districts.index')->with([
            'type'    => 'success',
            'message' => __('District created.'),
        ]);
    }

    public function edit(ServiceAreaDistrict $district): View
    {
        $regions = ServiceAreaRegion::orderBy('name')->get();

        return view('default.panel.user.team.service-areas.districts.form', [
            'district' => $district,
            'regions'  => $regions,
        ]);
    }

    public function update(Request $request, ServiceAreaDistrict $district): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'region_id'       => ['nullable', 'integer', 'exists:service_area_regions,id'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $district->update($validated);

        return redirect()->route('dashboard.team.service-area-districts.show', $district)->with([
            'type'    => 'success',
            'message' => __('District updated.'),
        ]);
    }

    public function destroy(ServiceAreaDistrict $district): RedirectResponse
    {
        $district->delete();

        return redirect()->route('dashboard.team.service-area-districts.index')->with([
            'type'    => 'success',
            'message' => __('District removed.'),
        ]);
    }
}
