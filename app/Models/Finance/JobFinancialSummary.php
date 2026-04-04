<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobFinancialSummary extends Model
{
    use BelongsToCompany;

    protected $table = 'job_financial_summaries';

    protected $fillable = [
        'job_id',
        'company_id',
        'total_cost',
        'total_revenue',
        'gross_margin',
        'gross_margin_pct',
        'labour_cost',
        'materials_cost',
        'travel_cost',
        'is_profitable',
        'calculated_at',
    ];

    protected $casts = [
        'total_cost'       => 'decimal:2',
        'total_revenue'    => 'decimal:2',
        'gross_margin'     => 'decimal:2',
        'gross_margin_pct' => 'decimal:4',
        'labour_cost'      => 'decimal:2',
        'materials_cost'   => 'decimal:2',
        'travel_cost'      => 'decimal:2',
        'is_profitable'    => 'boolean',
        'calculated_at'    => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function costRecords(): HasMany
    {
        return $this->hasMany(JobCostRecord::class, 'job_id', 'job_id');
    }

    public function revenueRecords(): HasMany
    {
        return $this->hasMany(JobRevenueRecord::class, 'job_id', 'job_id');
    }
}
