<?php

declare(strict_types=1);

namespace App\Models\FSM;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FsmJobStatusMeta — per-job kanban intelligence overlay.
 *
 * Stores the computed readiness and dispatch enrichment flags for a service
 * job.  Written by KanbanStatusService and consumed by the dispatch board,
 * EasyDispatch engine, and calendar overlay.
 */
class FsmJobStatusMeta extends Model
{
    use BelongsToCompany;

    protected $table = 'fsm_job_status_meta';

    protected $fillable = [
        'company_id',
        'service_job_id',
        'is_ready_to_start',
        'is_waiting_parts',
        'is_blocked',
        'is_overdue',
        'requires_followup',
        'customer_action_pending',
        'priority_score',
        'delay_risk',
        'travel_conflict_flag',
        'crew_skill_mismatch',
        'equipment_missing',
        'contract_violation',
        'equipment_warranty_expired',
        'agreement_expired',
        'vip_client_flag',
        'technician_prep_done',
        'refreshed_at',
    ];

    protected $casts = [
        'is_ready_to_start'         => 'boolean',
        'is_waiting_parts'          => 'boolean',
        'is_blocked'                => 'boolean',
        'is_overdue'                => 'boolean',
        'requires_followup'         => 'boolean',
        'customer_action_pending'   => 'boolean',
        'delay_risk'                => 'boolean',
        'travel_conflict_flag'      => 'boolean',
        'crew_skill_mismatch'       => 'boolean',
        'equipment_missing'         => 'boolean',
        'contract_violation'        => 'boolean',
        'equipment_warranty_expired'=> 'boolean',
        'agreement_expired'         => 'boolean',
        'vip_client_flag'           => 'boolean',
        'technician_prep_done'      => 'boolean',
        'priority_score'            => 'integer',
        'refreshed_at'              => 'datetime',
    ];

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }
}
