<?php

declare(strict_types=1);

namespace App\Models\FSM;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FsmJobPriorityScore — composite dispatch priority score for a service job.
 *
 * Computed by KanbanStatusService and consumed by EasyDispatch,
 * RouteOptimizer, and the dispatch board smart-sort.
 */
class FsmJobPriorityScore extends Model
{
    use BelongsToCompany;

    protected $table = 'fsm_job_priority_scores';

    protected $fillable = [
        'company_id',
        'service_job_id',
        'urgency_score',
        'sla_score',
        'client_tier_score',
        'agreement_score',
        'equipment_score',
        'total_score',
        'score_breakdown',
        'scored_at',
    ];

    protected $casts = [
        'urgency_score'    => 'integer',
        'sla_score'        => 'integer',
        'client_tier_score'=> 'integer',
        'agreement_score'  => 'integer',
        'equipment_score'  => 'integer',
        'total_score'      => 'integer',
        'score_breakdown'  => 'array',
        'scored_at'        => 'datetime',
    ];

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }
}
