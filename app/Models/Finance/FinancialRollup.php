<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class FinancialRollup extends Model
{
    use BelongsToCompany;

    protected $table = 'financial_rollups';

    protected $fillable = [
        'company_id',
        'rollup_type',
        'rollup_key',
        'period_start',
        'period_end',
        'job_count',
        'total_cost',
        'total_revenue',
        'gross_margin',
        'gross_margin_pct',
        'calculated_at',
    ];

    protected $casts = [
        'period_start'     => 'date',
        'period_end'       => 'date',
        'job_count'        => 'integer',
        'total_cost'       => 'decimal:2',
        'total_revenue'    => 'decimal:2',
        'gross_margin'     => 'decimal:2',
        'gross_margin_pct' => 'decimal:4',
        'calculated_at'    => 'datetime',
    ];

    public const ROLLUP_TYPES = ['customer', 'premises', 'agreement', 'technician', 'job_type', 'territory', 'month'];
}
