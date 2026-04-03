<?php

namespace Modules\Workflow\Entities;

use Illuminate\Database\Eloquent\Model;

class WorkflowRunStep extends Model
{
    protected $table = 'workflow_run_steps';
    protected $guarded = [];
    protected $casts = [
        'config' => 'array',
    ];
}
