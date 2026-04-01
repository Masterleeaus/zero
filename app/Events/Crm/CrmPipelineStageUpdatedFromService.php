<?php

declare(strict_types=1);

namespace App\Events\Crm;

use App\Models\Work\JobStage;
use App\Models\Work\ServiceJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a ServiceJob stage change should be reflected back into the CRM pipeline.
 *
 * Module 6 (fieldservice_crm) — crm_pipeline_stage_updated_from_service signal.
 */
class CrmPipelineStageUpdatedFromService
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ServiceJob $job,
        public readonly ?JobStage $previousStage,
        public readonly JobStage $newStage,
    ) {}
}
