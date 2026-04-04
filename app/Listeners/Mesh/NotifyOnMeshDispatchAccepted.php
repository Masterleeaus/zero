<?php

declare(strict_types=1);

namespace App\Listeners\Mesh;

use App\Events\Mesh\MeshDispatchAccepted;
use Illuminate\Support\Facades\Log;

class NotifyOnMeshDispatchAccepted
{
    public function handle(MeshDispatchAccepted $event): void
    {
        // Notification placeholder — extend with mail/SMS/push when notification
        // channels are available. For now we emit a structured log entry that
        // can be picked up by the monitoring stack.
        Log::info('MeshDispatchAccepted', [
            'mesh_dispatch_request_id' => $event->request->id,
            'requesting_company_id'    => $event->request->requesting_company_id,
            'fulfilling_company_id'    => $event->request->fulfilling_company_id,
            'mesh_job_reference'       => $event->request->mesh_job_reference,
            'accepted_at'              => $event->request->accepted_at,
        ]);
    }
}
