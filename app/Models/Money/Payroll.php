<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Work\StaffProfile;
use App\Models\Work\TimesheetSubmission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Payroll Run — a payroll period for one or more staff members.
 *
 * Bridges Work/StaffProfile + Work/TimesheetSubmission to the Money ledger.
 * Statuses: draft | processing | approved | paid | cancelled
 *
 * Journal auto-posting (Phase 7):
 *   On approval: Dr Wages Expense / Cr Bank Account + Cr Tax Payable
 */
class Payroll extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    public const STATUS_DRAFT      = 'draft';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_APPROVED   = 'approved';
    public const STATUS_PAID       = 'paid';
    public const STATUS_CANCELLED  = 'cancelled';

    protected $fillable = [
        'company_id',
        'created_by',
        'reference',
        'period_start',
        'period_end',
        'pay_date',
        'status',
        'total_gross',
        'total_tax',
        'total_deductions',
        'total_net',
        'currency',
        'notes',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'period_start'     => 'date',
        'period_end'       => 'date',
        'pay_date'         => 'date',
        'approved_at'      => 'datetime',
        'paid_at'          => 'datetime',
        'total_gross'      => 'decimal:2',
        'total_tax'        => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net'        => 'decimal:2',
    ];

    protected $attributes = [
        'status'           => self::STATUS_DRAFT,
        'total_gross'      => 0,
        'total_tax'        => 0,
        'total_deductions' => 0,
        'total_net'        => 0,
        'currency'         => 'AUD',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function lines(): HasMany
    {
        return $this->hasMany(PayrollLine::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function recalculate(): void
    {
        $this->total_gross      = (float) $this->lines()->sum('gross_pay');
        $this->total_tax        = (float) $this->lines()->sum('tax_amount');
        $this->total_deductions = (float) $this->lines()->sum('deductions');
        $this->total_net        = (float) $this->lines()->sum('net_pay');
        $this->save();
    }
}
