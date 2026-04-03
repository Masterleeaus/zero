<?php

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DispatchAssignment extends Model
{
    use BelongsToCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'job_id',
        'technician_id',
        'assigned_by',
        'constraint_score',
        'travel_estimate_mins',
        'assigned_at',
        'confirmed_at',
        'status',
    ];

    protected $casts = [
        'assigned_at'      => 'datetime',
        'confirmed_at'     => 'datetime',
        'constraint_score' => 'decimal:2',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
