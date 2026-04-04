<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobCostRecord extends Model
{
    use BelongsToCompany;

    protected $table = 'job_cost_records';

    protected $fillable = [
        'company_id',
        'job_id',
        'cost_type',
        'description',
        'quantity',
        'unit_cost',
        'total_cost',
        'recorded_by',
        'cost_date',
        'is_billable',
    ];

    protected $casts = [
        'quantity'    => 'decimal:3',
        'unit_cost'   => 'decimal:4',
        'total_cost'  => 'decimal:2',
        'cost_date'   => 'date',
        'is_billable' => 'boolean',
    ];

    public const COST_TYPES = ['labour', 'materials', 'travel', 'subcontract', 'overhead', 'other'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
