<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\User;
use App\Models\Work\Branch;
use App\Models\Work\District;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchController extends CoreController
{
    public function index(): View
    {
        $branches = Branch::with('district.region', 'manager')
            ->withCount('territories')
            ->forSelect()
            ->paginate(30);

        return view('default.panel.user.team.branches.index', [
            'branches' => $branches,
        ]);
    }

    public function create(): View
    {
        return view('default.panel.user.team.branches.form', [
            'branch'    => new Branch(),
            'districts' => District::forSelect()->get(),
            'managers'  => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'district_id'     => ['nullable', 'integer', 'exists:districts,id'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        Branch::create($validated);

        return redirect()->route('dashboard.team.branches.index')->with([
            'type'    => 'success',
            'message' => __('Branch created.'),
        ]);
    }

    public function show(Branch $branch): View
    {
        $branch->load('district.region', 'manager', 'territories');

        return view('default.panel.user.team.branches.show', [
            'branch' => $branch,
        ]);
    }

    public function edit(Branch $branch): View
    {
        return view('default.panel.user.team.branches.form', [
            'branch'    => $branch,
            'districts' => District::forSelect()->get(),
            'managers'  => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'district_id'     => ['nullable', 'integer', 'exists:districts,id'],
            'manager_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $branch->update($validated);

        return redirect()->route('dashboard.team.branches.show', $branch)->with([
            'type'    => 'success',
            'message' => __('Branch updated.'),
        ]);
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()->route('dashboard.team.branches.index')->with([
            'type'    => 'success',
            'message' => __('Branch removed.'),
        ]);
    }
}
