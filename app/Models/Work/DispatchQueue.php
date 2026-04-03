<?php

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchQueue extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'job_id',
        'priority_score',
        'queued_at',
        'attempts',
        'last_attempt_at',
    ];

    protected $casts = [
        'queued_at'       => 'datetime',
        'last_attempt_at' => 'datetime',
        'priority_score'  => 'decimal:2',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class);
    }
}
