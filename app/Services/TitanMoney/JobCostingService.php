<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Expense;
use App\Models\Money\JobCostAllocation;
use App\Models\Money\Payroll;
use App\Models\Money\SupplierBillLine;
use App\Models\Work\ServiceJob;
use App\Models\Work\TimesheetSubmission;
use Carbon\Carbon;

class JobCostingService
{
    /** Map expense cost_bucket → JobCostAllocation cost_type */
    private const BUCKET_TO_COST_TYPE = [
        'overhead'       => 'overhead',
        'labor_adjacent' => 'labour',
        'reimbursable'   => 'reimbursable',
        'materials'      => 'material',
        'transport'      => 'material',
        'equipment'      => 'equipment',
        'subcontractor'  => 'subcontractor',
        'admin'          => 'admin',
        'tax_adjacent'   => 'overhead',
    ];

    public function allocateExpense(Expense $expense): JobCostAllocation
    {
        $costType = self::BUCKET_TO_COST_TYPE[$expense->cost_bucket ?? ''] ?? 'overhead';

        return JobCostAllocation::create([
            'company_id'     => $expense->company_id,
            'service_job_id' => $expense->service_job_id,
            'site_id'        => $expense->site_id,
            'team_id'        => $expense->team_id,
            'supplier_id'    => $expense->supplier_id,
            'source_type'    => 'expense',
            'source_id'      => $expense->id,
            'cost_type'      => $costType,
            'amount'         => $expense->amount,
            'description'    => $expense->title,
            'allocated_at'   => $expense->expense_date ?? Carbon::today(),
            'created_by'     => $expense->created_by,
        ]);
    }

    public function allocateSupplierBillLine(SupplierBillLine $line, ?int $serviceJobId = null): JobCostAllocation
    {
        $bill = $line->bill;

        return JobCostAllocation::create([
            'company_id'     => $bill->company_id,
            'service_job_id' => $serviceJobId,
            'supplier_id'    => $bill->supplier_id ?? null,
            'source_type'    => 'supplier_bill_line',
            'source_id'      => $line->id,
            'cost_type'      => 'material',
            'amount'         => $line->amount ?? ($line->quantity * $line->unit_price),
            'quantity'       => $line->quantity,
            'unit_cost'      => $line->unit_price,
            'description'    => $line->description,
            'allocated_at'   => $bill->bill_date ?? Carbon::today(),
        ]);
    }

    public function allocateTimesheetLabor(TimesheetSubmission $ts, float $hourlyRate, ?int $serviceJobId = null): JobCostAllocation
    {
        $hours  = (float) ($ts->total_hours ?? 0);
        $amount = round($hours * $hourlyRate, 2);

        return JobCostAllocation::create([
            'company_id'     => $ts->company_id,
            'service_job_id' => $serviceJobId ?? ($ts->service_job_id ?? null),
            'source_type'    => 'timesheet',
            'source_id'      => $ts->id,
            'cost_type'      => 'labour',
            'amount'         => $amount,
            'quantity'       => $hours,
            'unit_cost'      => $hourlyRate,
            'description'    => "Labour: {$ts->user_id} — {$hours}h @ \${$hourlyRate}/h",
            'allocated_at'   => $ts->week_start ?? Carbon::today(),
            'created_by'     => $ts->user_id,
        ]);
    }

    public function allocatePayrollRun(Payroll $payroll, ?int $serviceJobId = null): JobCostAllocation
    {
        return JobCostAllocation::create([
            'company_id'     => $payroll->company_id,
            'service_job_id' => $serviceJobId,
            'source_type'    => 'payroll_run',
            'source_id'      => $payroll->id,
            'cost_type'      => 'labour',
            'amount'         => (float) $payroll->total_gross,
            'description'    => "Payroll run #{$payroll->id}",
            'allocated_at'   => $payroll->pay_period_end ?? Carbon::today(),
        ]);
    }

    public function allocateManual(array $data, int $companyId, int $createdBy): JobCostAllocation
    {
        return JobCostAllocation::create(array_merge($data, [
            'company_id'  => $companyId,
            'source_type' => $data['source_type'] ?? 'manual_adjustment',
            'created_by'  => $createdBy,
        ]));
    }

    public function totalForJob(ServiceJob $job): float
    {
        return (float) JobCostAllocation::query()
            ->forJob($job->id)
            ->sum('amount');
    }

    public function breakdownForJob(ServiceJob $job): array
    {
        return JobCostAllocation::query()
            ->forJob($job->id)
            ->selectRaw('cost_type, SUM(amount) as total')
            ->groupBy('cost_type')
            ->pluck('total', 'cost_type')
            ->toArray();
    }

    public function allocationsForJob(ServiceJob $job)
    {
        return JobCostAllocation::query()->forJob($job->id);
    }
}
