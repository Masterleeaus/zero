<?php

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCostAllocation extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'job_cost_allocations';

    protected $guarded = [];

    const SOURCE_TYPES = [
        'expense',
        'supplier_bill_line',
        'timesheet',
        'payroll_run',
        'inventory_usage',
        'asset_usage',
        'manual_adjustment',
    ];

    const COST_TYPES = [
        'labour',
        'material',
        'equipment',
        'subcontractor',
        'overhead',
        'reimbursable',
        'admin',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'quantity'     => 'decimal:4',
        'unit_cost'    => 'decimal:2',
        'posted'       => 'boolean',
        'posted_at'    => 'datetime',
        'allocated_at' => 'date',
    ];

    public function serviceJob()
    {
        return $this->belongsTo(ServiceJob::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function isPosted(): bool
    {
        return (bool) $this->posted;
    }

    public function scopeForJob($query, $jobId)
    {
        return $query->where('service_job_id', $jobId);
    }

    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeUnposted($query)
    {
        return $query->where('posted', false);
    }

    public function scopeBySourceType($query, string $type)
    {
        return $query->where('source_type', $type);
    }

    public function scopeByCostType($query, string $type)
    {
        return $query->where('cost_type', $type);
    }
}
