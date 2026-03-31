<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\User;
use App\Models\Work\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegionController extends CoreController
{
    public function index(): View
    {
        $regions = Region::with('manager')
            ->withCount('districts')
            ->forSelect()
            ->paginate(30);

        return view('default.panel.user.team.regions.index', [
            'regions' => $regions,
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.team.regions.form', [
            'region'   => new Region(),
            'managers' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        Region::create($validated);

        return redirect()->route('dashboard.team.regions.index')->with([
            'type'    => 'success',
            'message' => __('Region created.'),
        ]);
    }

    public function show(Region $region): View
    {
        $region->load('manager', 'districts.branches');

        return view('default.panel.user.team.regions.show', [
            'region' => $region,
        ]);
    }

    public function edit(Region $region): View
    {
        return view('default.panel.user.team.regions.form', [
            'region'   => $region,
            'managers' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Region $region): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $region->update($validated);

        return redirect()->route('dashboard.team.regions.show', $region)->with([
            'type'    => 'success',
            'message' => __('Region updated.'),
        ]);
    }

    public function destroy(Region $region): RedirectResponse
    {
        $region->delete();

        return redirect()->route('dashboard.team.regions.index')->with([
            'type'    => 'success',
            'message' => __('Region removed.'),
        ]);
    }
}
