<?php

namespace App\Listeners\Work;

use App\Events\Work\JobDispatched;
use App\Titan\Signals\AuditTrail;
use Illuminate\Support\Facades\Auth;

class RecordDispatchAuditTrail
{
    public function __construct(protected AuditTrail $auditTrail) {}

    public function handle(JobDispatched $event): void
    {
        $this->auditTrail->recordEntry(
            "dispatch:{$event->job->id}",
            'dispatch.allocated',
            [
                'job_id'           => $event->job->id,
                'technician_id'    => $event->assignment->technician_id,
                'constraint_score' => $event->assignment->constraint_score,
                'assigned_by'      => $event->assignment->assigned_by,
            ],
            null,
            Auth::id(),
        );
    }
}
