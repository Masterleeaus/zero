<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Work\StaffProfile;
use App\Models\Work\TimesheetSubmission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single employee line within a Payroll run.
 *
 * Bridges StaffProfile (salary/rate) and optionally a TimesheetSubmission (hours).
 */
class PayrollLine extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'payroll_id',
        'staff_profile_id',
        'timesheet_submission_id',
        'employee_name',
        'hours_worked',
        'hourly_rate',
        'gross_pay',
        'tax_amount',
        'deductions',
        'net_pay',
        'notes',
    ];

    protected $casts = [
        'hours_worked'  => 'decimal:2',
        'hourly_rate'   => 'decimal:2',
        'gross_pay'     => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'deductions'    => 'decimal:2',
        'net_pay'       => 'decimal:2',
    ];

    protected $attributes = [
        'hours_worked'  => 0,
        'hourly_rate'   => 0,
        'gross_pay'     => 0,
        'tax_amount'    => 0,
        'deductions'    => 0,
        'net_pay'       => 0,
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function timesheetSubmission(): BelongsTo
    {
        return $this->belongsTo(TimesheetSubmission::class, 'timesheet_submission_id');
    }

    // -----------------------------------------------------------------------
    // Hooks
    // -----------------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (self $line): void {
            // Recompute net_pay from gross minus deductions and tax
            $line->net_pay = round(
                (float) $line->gross_pay - (float) $line->tax_amount - (float) $line->deductions,
                2
            );
        });
    }
}
