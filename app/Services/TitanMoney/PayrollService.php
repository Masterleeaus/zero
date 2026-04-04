<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Payroll;
use App\Models\Money\PayrollLine;
use App\Models\Work\StaffProfile;
use App\Models\Work\TimesheetSubmission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * PayrollService — payroll run lifecycle management.
 *
 * Responsibilities:
 *   - Create a payroll run for a period
 *   - Add employee lines (from StaffProfile + optional TimesheetSubmission)
 *   - Approve payroll (triggers ledger posting hook)
 *   - Prevent duplicate runs for the same period
 */
class PayrollService
{
    /**
     * Create a payroll run header for a company/period.
     *
     * @param  array{
     *   company_id: int,
     *   period_start: string,
     *   period_end: string,
     *   pay_date: string,
     *   reference?: string,
     *   notes?: string,
     *   created_by?: int,
     * } $payload
     */
    public function createRun(array $payload): Payroll
    {
        // Block duplicate runs for the same period
        $exists = Payroll::where('company_id', $payload['company_id'])
            ->where('period_start', $payload['period_start'])
            ->where('period_end', $payload['period_end'])
            ->whereNotIn('status', [Payroll::STATUS_CANCELLED])
            ->exists();

        if ($exists) {
            throw new RuntimeException(
                "A payroll run already exists for period {$payload['period_start']} – {$payload['period_end']}."
            );
        }

        return Payroll::create([
            'company_id'   => $payload['company_id'],
            'created_by'   => $payload['created_by'] ?? null,
            'reference'    => $payload['reference'] ?? $this->makeReference($payload),
            'period_start' => $payload['period_start'],
            'period_end'   => $payload['period_end'],
            'pay_date'     => $payload['pay_date'],
            'notes'        => $payload['notes'] ?? null,
            'status'       => Payroll::STATUS_DRAFT,
        ]);
    }

    /**
     * Add a staff member's pay line to a payroll run.
     *
     * Will use hourly_rate from StaffProfile and hours from TimesheetSubmission (if supplied).
     * Falls back to salary / pay_frequency when no timesheet is attached.
     *
     * @param  array{
     *   staff_profile_id: int,
     *   timesheet_submission_id?: int,
     *   tax_amount?: float,
     *   deductions?: float,
     *   notes?: string,
     * } $payload
     */
    public function addLine(Payroll $payroll, array $payload): PayrollLine
    {
        if (! $payroll->isDraft()) {
            throw new RuntimeException('Lines can only be added to a draft payroll run.');
        }

        $profile = StaffProfile::findOrFail($payload['staff_profile_id']);

        // Determine gross pay
        $grossPay = 0.0;
        $hours    = 0.0;
        $rate     = (float) ($profile->hourly_rate ?? 0);

        if (! empty($payload['timesheet_submission_id'])) {
            $timesheet = TimesheetSubmission::findOrFail($payload['timesheet_submission_id']);
            $hours     = (float) $timesheet->total_hours;
            $grossPay  = round($hours * $rate, 2);
        } elseif ($profile->salary) {
            // Salary-based: divide annual by pay frequency periods per year
            $periods  = $this->periodsPerYear($profile->pay_frequency ?? 'monthly');
            $grossPay = round((float) $profile->salary / $periods, 2);
        } else {
            $grossPay = round($hours * $rate, 2);
        }

        $tax        = (float) ($payload['tax_amount'] ?? 0.0);
        $deductions = (float) ($payload['deductions'] ?? 0.0);

        $line = PayrollLine::create([
            'company_id'              => $payroll->company_id,
            'payroll_id'              => $payroll->id,
            'staff_profile_id'        => $profile->id,
            'timesheet_submission_id' => $payload['timesheet_submission_id'] ?? null,
            'employee_name'           => $profile->user?->name ?? "Staff #{$profile->id}",
            'hours_worked'            => $hours,
            'hourly_rate'             => $rate,
            'gross_pay'               => $grossPay,
            'tax_amount'              => $tax,
            'deductions'              => $deductions,
            'notes'                   => $payload['notes'] ?? null,
        ]);

        $payroll->recalculate();

        return $line;
    }

    /**
     * Approve a payroll run (moves to approved status, fires posting hook).
     */
    public function approve(Payroll $payroll, int $approvedBy): Payroll
    {
        if (! $payroll->isDraft()) {
            throw new RuntimeException("Only draft payrolls can be approved. Status: {$payroll->status}");
        }

        if ($payroll->lines()->count() === 0) {
            throw new RuntimeException('Cannot approve an empty payroll run.');
        }

        $payroll->update([
            'status'      => Payroll::STATUS_APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $payroll->fresh();
    }

    // -----------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------

    private function makeReference(array $payload): string
    {
        return 'PAY-' . date('Ym', strtotime($payload['period_start']));
    }

    private function periodsPerYear(string $frequency): int
    {
        return match (strtolower($frequency)) {
            'weekly'     => 52,
            'fortnightly', 'biweekly' => 26,
            'monthly'    => 12,
            default      => 12,
        };
    }
}
