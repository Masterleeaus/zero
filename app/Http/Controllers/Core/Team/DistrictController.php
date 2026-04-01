<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\User;
use App\Models\Work\District;
use App\Models\Work\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DistrictController extends CoreController
{
    public function index(): View
    {
        $districts = District::with('region', 'manager')
            ->withCount('branches')
            ->forSelect()
            ->paginate(30);

        return view('default.panel.user.team.districts.index', [
            'districts' => $districts,
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.team.districts.form', [
            'district' => new District(),
            'regions'  => Region::forSelect()->get(),
            'managers' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'region_id'       => ['nullable', 'integer', 'exists:regions,id'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        District::create($validated);

        return redirect()->route('dashboard.team.districts.index')->with([
            'type'    => 'success',
            'message' => __('District created.'),
        ]);
    }

    public function show(District $district): View
    {
        $district->load('region', 'manager', 'branches.territories');

        return view('default.panel.user.team.districts.show', [
            'district' => $district,
        ]);
    }

    public function edit(District $district): View
    {
        return view('default.panel.user.team.districts.form', [
            'district' => $district,
            'regions'  => Region::forSelect()->get(),
            'managers' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, District $district): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'region_id'       => ['nullable', 'integer', 'exists:regions,id'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $district->update($validated);

        return redirect()->route('dashboard.team.districts.show', $district)->with([
            'type'    => 'success',
            'message' => __('District updated.'),
        ]);
    }

    public function destroy(District $district): RedirectResponse
    {
        $district->delete();

        return redirect()->route('dashboard.team.districts.index')->with([
            'type'    => 'success',
            'message' => __('District removed.'),
        ]);
    }
}
