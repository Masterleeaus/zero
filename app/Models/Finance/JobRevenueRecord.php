<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRevenueRecord extends Model
{
    use BelongsToCompany;

    protected $table = 'job_revenue_records';

    protected $fillable = [
        'company_id',
        'job_id',
        'revenue_type',
        'description',
        'quantity',
        'unit_price',
        'total_revenue',
        'billing_source',
        'agreement_id',
        'is_invoiced',
        'invoiced_at',
    ];

    protected $casts = [
        'quantity'      => 'decimal:3',
        'unit_price'    => 'decimal:4',
        'total_revenue' => 'decimal:2',
        'is_invoiced'   => 'boolean',
        'invoiced_at'   => 'datetime',
    ];

    public const REVENUE_TYPES    = ['labour', 'materials', 'call_out', 'surcharge', 'contract_allocation', 'other'];
    public const BILLING_SOURCES  = ['agreement', 'quote', 'ad_hoc', 'time_and_materials'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }
}
