<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::with('employee.user')->latest()->paginate(20);
        $employees = Employee::with('user')->where('status', 'active')->get();
        return view('admin.payroll.index', compact('payrolls', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|string',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
        ]);

        // Check if already generated for this month
        $exists = Payroll::where('employee_id', $validated['employee_id'])
            ->where('month', $validated['month'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Payroll already generated for this employee for ' . $validated['month']);
        }

        $emp = Employee::findOrFail($validated['employee_id']);
        $basic = $emp->basic_salary;
        $bonus = $validated['bonus'] ?? 0;
        $deduction = $validated['deduction'] ?? 0;
        $net = $basic + $bonus - $deduction;

        Payroll::create([
            'employee_id' => $validated['employee_id'],
            'month' => $validated['month'],
            'basic_salary' => $basic,
            'bonus' => $bonus,
            'deduction' => $deduction,
            'net_salary' => $net,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Payroll slip generated successfully.');
    }

    public function update(Request $request, Payroll $payroll)
    {
        $payroll->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        return back()->with('success', 'Salary marked as paid.');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee.user', 'employee.store');
        return view('admin.payroll.show', compact('payroll'));
    }
}
