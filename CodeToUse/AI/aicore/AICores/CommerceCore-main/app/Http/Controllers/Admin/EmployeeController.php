<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Traits\ResolvesStore;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ResolvesStore;

    public function index(Request $request)
    {
        $store     = $this->getActiveStore($request);
        $employees = Employee::where('store_id', $store->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $store = $this->getActiveStore($request);

        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id|unique:employees,user_id',
            'designation'  => 'required|string|max:255',
            'basic_salary' => 'required|numeric',
            'joining_date' => 'required|date',
            'employee_id'  => 'required|string|unique:employees,employee_id',
        ]);

        Employee::create(array_merge($validated, [
            'store_id' => $store->id,
            'status'   => 'active',
        ]));

        return back()->with('success', 'Employee registered successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load('user', 'payrolls', 'attendances');
        return view('admin.employees.show', compact('employee'));
    }
}
