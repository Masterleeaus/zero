<?php

namespace Modules\Workflow\Entities;

use Illuminate\Database\Eloquent\Model;

class WorkflowRun extends Model
{
    protected $table = 'workflow_runs';
    protected $guarded = [];
    protected $casts = [
        'event_payload' => 'array',
    ];
}
