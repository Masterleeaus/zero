<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\ServiceAreaBranch;
use App\Models\Work\ServiceAreaDistrict;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceAreaBranchController extends CoreController
{
    public function index(): View
    {
        $branches = ServiceAreaBranch::with('district.region')
            ->withCount('serviceAreas')
            ->orderBy('name')
            ->get();

        return view('default.panel.user.team.service-areas.branches.index', [
            'branches' => $branches,
        ]);
    }

    public function create(): View
    {
        $districts = ServiceAreaDistrict::with('region')->orderBy('name')->get();

        return view('default.panel.user.team.service-areas.branches.form', [
            'branch'    => null,
            'districts' => $districts,
        ]);
    }

    public function show(ServiceAreaBranch $branch): View
    {
        $branch->loadCount('serviceAreas')->load('district.region', 'serviceAreas');

        return view('default.panel.user.team.service-areas.branches.show', [
            'branch' => $branch,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'district_id'     => ['nullable', 'integer', 'exists:service_area_districts,id'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        ServiceAreaBranch::create($validated);

        return redirect()->route('dashboard.team.service-area-branches.index')->with([
            'type'    => 'success',
            'message' => __('Branch created.'),
        ]);
    }

    public function edit(ServiceAreaBranch $branch): View
    {
        $districts = ServiceAreaDistrict::with('region')->orderBy('name')->get();

        return view('default.panel.user.team.service-areas.branches.form', [
            'branch'    => $branch,
            'districts' => $districts,
        ]);
    }

    public function update(Request $request, ServiceAreaBranch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'district_id'     => ['nullable', 'integer', 'exists:service_area_districts,id'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $branch->update($validated);

        return redirect()->route('dashboard.team.service-area-branches.show', $branch)->with([
            'type'    => 'success',
            'message' => __('Branch updated.'),
        ]);
    }

    public function destroy(ServiceAreaBranch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()->route('dashboard.team.service-area-branches.index')->with([
            'type'    => 'success',
            'message' => __('Branch removed.'),
        ]);
    }
}
