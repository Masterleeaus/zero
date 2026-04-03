<?php

declare(strict_types=1);

namespace App\Http\Controllers\Core\Money;

use App\Http\Controllers\Core\CoreController;
use App\Models\Money\Payroll;
use App\Models\Work\StaffProfile;
use App\Models\Work\TimesheetSubmission;
use App\Services\TitanMoney\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends CoreController
{
    public function __construct(private readonly PayrollService $service) {}

    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $payrolls = Payroll::where('company_id', $companyId)
            ->latest('period_start')
            ->paginate(25)
            ->withQueryString();

        return view('default.panel.user.money.payroll.index', compact('payrolls'));
    }

    public function create(Request $request): View
    {
        return view('default.panel.user.money.payroll.create', [
            'payroll' => new Payroll(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'pay_date'     => 'required|date',
            'reference'    => 'nullable|string|max:100',
            'notes'        => 'nullable|string',
        ]);

        $payroll = $this->service->createRun(array_merge($validated, [
            'company_id' => $companyId,
            'created_by' => $request->user()->id,
        ]));

        return redirect()
            ->route('dashboard.money.payroll.show', $payroll)
            ->with('success', __('Payroll run created.'));
    }

    public function show(Request $request, Payroll $payroll): View
    {
        abort_if($payroll->company_id !== $request->user()->company_id, 403);

        $companyId = $request->user()->company_id;

        $payroll->load(['lines.staffProfile', 'lines.timesheetSubmission', 'approver']);

        $staffProfiles = StaffProfile::where('company_id', $companyId)
            ->where('status', 'active')
            ->with('user')
            ->get();

        $timesheets = TimesheetSubmission::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereBetween('week_start', [$payroll->period_start, $payroll->period_end])
            ->get();

        return view('default.panel.user.money.payroll.show', compact('payroll', 'staffProfiles', 'timesheets'));
    }

    public function addLine(Request $request, Payroll $payroll): RedirectResponse
    {
        abort_if($payroll->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'staff_profile_id'        => 'required|integer',
            'timesheet_submission_id'  => 'nullable|integer',
            'tax_amount'               => 'nullable|numeric|min:0',
            'deductions'               => 'nullable|numeric|min:0',
            'notes'                    => 'nullable|string',
        ]);

        $this->service->addLine($payroll, $validated);

        return back()->with('success', __('Employee added to payroll run.'));
    }

    public function approve(Request $request, Payroll $payroll): RedirectResponse
    {
        abort_if($payroll->company_id !== $request->user()->company_id, 403);

        $this->service->approve($payroll, $request->user()->id);

        return back()->with('success', __('Payroll run approved.'));
    }
}
