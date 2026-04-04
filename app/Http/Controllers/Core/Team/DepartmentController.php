<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Team;

use App\Http\Controllers\Core\CoreController;
use App\Models\Work\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DepartmentController extends CoreController
{
    public function index(Request $request): View
    {
        $departments = Department::query()
            ->where('company_id', $request->user()->company_id)
            ->with('parent')
            ->latest()
            ->paginate(20);

        return $this->placeholder('Departments', 'Manage company departments');
    }

    public function create(Request $request): View
    {
        abort_if($request->user()->company_id === null, 403);

        return $this->placeholder('Create Department');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->company_id === null, 403);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'integer'],
            'status'    => ['nullable', 'string'],
        ]);

        $data['company_id'] = $request->user()->company_id;
        $data['status']     = $data['status'] ?? 'active';

        Department::create($data);

        return redirect()->route('dashboard.team.departments.index')
            ->with('success', 'Department created.');
    }

    public function show(Request $request, Department $department): View
    {
        abort_if($department->company_id !== $request->user()->company_id, 403);

        return $this->placeholder('Department: ' . $department->name);
    }

    public function edit(Request $request, Department $department): View
    {
        abort_if($department->company_id !== $request->user()->company_id, 403);
        abort_if(! $request->user()->isAdmin(), 403);

        return $this->placeholder('Edit Department: ' . $department->name);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        abort_if($department->company_id !== $request->user()->company_id, 403);
        abort_if(! $request->user()->isAdmin(), 403);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'integer'],
            'status'    => ['nullable', 'string'],
        ]);

        $department->update($data);

        return redirect()->route('dashboard.team.departments.index')
            ->with('success', 'Department updated.');
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        abort_if($department->company_id !== $request->user()->company_id, 403);
        abort_if(! $request->user()->isAdmin(), 403);

        $department->delete();

        return redirect()->route('dashboard.team.departments.index')
            ->with('success', 'Department deleted.');
    }
}
